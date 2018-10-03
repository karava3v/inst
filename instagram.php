<?php

require_once "vendor/autoload.php";
use instagram\helpers\File;
use instagram\helpers\Request;
use instagram\models\Profile;
use instagram\helpers\Frequency;

const COMMENT_DATE = 0;
const COMMENT_TEXT = 1;
const COMMENT_TO = 2;

const LIKE_DATE = 0;
const LIKE_TO = 1;

$users = []; $likes_new_users = []; $comments_new_users = []; $messages_new_users = [];
$errors = [];

$File = new File();

$params = array(
    ''   => 'help',
    'z:' => 'zip:',
    'u:' => 'user:'
);

$zip = null;
$user = null;

$options = getopt(implode('', array_keys($params)), $params);

if (isset($options['zip']) || isset($options['z'])) {
    $zip = isset($options['zip']) ? $options['zip'] : $options['z'];
} else {
    $errors[] = 'zip required';
}

if (isset($options['user']) || isset($options['u'])) {
    $user = isset($options['user']) ? $options['user'] : $options['u'];
} else {
    $errors[] = 'user required';
}

if (isset($options['help']) || count($errors)) {
    $help = "
    usage: php instagram.php [--help] [-u|--user=instagram_nickname] [-z|--zip=zip_name.zip]

    Options:
                --help      Show this message
            -z  --zip       Zip filename
            -u  --user      Instagram username
    Example:
            php instagram.php --zip=instagram_20180823.zip --user=nekaravaev
    ";

    if ($errors) {
        $help .= 'Errors:   ' . PHP_EOL . implode(", ", $errors) . PHP_EOL;
    }

    die($help);
}


$viewsOrder = [
    'profile',
    'settings',
    'media',
    'saved',
    'searches',
    'messages',
    'contacts',
    'comments',
    'likes',
    'connections'
];

$views = [];

$zipPath = getcwd() . "/$zip";

$ext = pathinfo($zipPath, PATHINFO_EXTENSION);
$filename = explode('.', $zip);

if ($File->checkExtension($ext) && $File->checkType($zipPath)) {

    $path = getcwd() . '/tmp/'. $filename[0];

    if (!is_dir($path)) {
        $File->createDir($path);
    }

    try {
        $path = $File->extractZip($zipPath, $path);
        echo "✍🏻 Successfully extracted. Processing data.." . PHP_EOL;
    } catch (\ErrorException $exception) {
        echo $exception->getMessage();
    }
} else {
    throw new \Exception('Invalid zip');
}


$list = $File->getListOfJsonFiles($path);

try {
    $File->checkForRequiredFiles($list);
} catch (\Error $e) {
    die($e->getMessage());
}

//twig
$loader = new Twig_Loader_Filesystem(getcwd() . '/src/views');
$twig = new Twig_Environment($loader, array(
    'cache' => false,
));

//user profile to object. It will be set as global

$profile = $File->getPathByFileName($list, 'profile');

if ( $profile ) {
    $Profile = new Profile;
    $Profile->fill( json_decode( file_get_contents( $profile ), true) );
    $twig->addGlobal('Profile', $Profile);

    echo "🕶 Profile @{$user}, zip is {$zip}" . PHP_EOL;
} else
    throw new Error('No profile found');

$users[$Profile->username] = $Profile;


/**
 * Work with messages, getting user's DM interlocutors for improve appearance
 */

$messages = $File->getPathByFileName($list, 'messages');

if ( $messages ) {
    echo "📧 Parse messages" . PHP_EOL;

    $messages_updated = [];
    $Request          = new Request();

    $messages = json_decode( file_get_contents( $messages ), true );

    foreach ($messages as $chat) {

        foreach ( $chat['participants']  as $participant) {

            if (! $Profile->searchInList($participant, $users)) {
                $messages_new_users[] = $participant;
            }
        }

        $messages_updated[] = [
            'participants' => $chat['participants'],
            'conversation' => array_reverse($chat['conversation'], true)
        ];
    }
}

/**
 * Comments
 */


$comments = $File->getPathByFileName($list, 'comments');

if ( $comments ) {
    echo "💭 Parse comments" . PHP_EOL;

    if (!isset( $Request ))
        $Request = new Request();

    $comments_list = json_decode( file_get_contents($comments), true );

    $CommentsFrequency = new Frequency( $comments_list, $Request, COMMENT_TO );

    $CommentsFrequency->setUsers($users);

    $comments_frequency = $CommentsFrequency->calculate();

    $comments_new_users = $CommentsFrequency->searchUsers( $user );
}

/**
 * Likes
 */

$likes = $File->getPathByFileName($list, 'likes');

if ( $likes ) {

    echo "💕 Now process likes". PHP_EOL;

    $likes_list = json_decode( file_get_contents($likes), true );

    if ( !isset( $Request ) )
        $Request = new Request();

    $LikesFrequency = new Frequency( $likes_list, $Request, LIKE_TO );

    $LikesFrequency->setUsers($users);

    $likes_frequency = $LikesFrequency->calculate();

    $likes_new_users = $LikesFrequency->searchUsers( $user );
}

/*
 * Adding new users to global array
 */

$new_users = array_unique(array_merge($messages_new_users, $comments_new_users));

if ( !empty($new_users) ) {

    $count_new_users = count($new_users);
    $counter = 0;

    echo "🔍 Found {$count_new_users} of user profiles. Fetching data from Instagram to load pics, names, etc..." . PHP_EOL;

    foreach ( $new_users as $user ) {
        $counter++;

        $Participant = new Profile();

        try {
            $participant_data = $Participant->setRequest( $Request )->getFromInstagram($user);
            $Participant->Fill( $participant_data );

            $users[$user]  = $Participant;

            echo "✅ Done with @{$user}! [{$counter}/{$count_new_users}]" . PHP_EOL;

        } catch (Exception $e) {
            echo "🚫 Can't get @{$user}'s profile.  [{$counter}/{$count_new_users}]";
            $errors[] = $e->getMessage();
        }
    }
}

/*
* for resolving path to static
*/
$url_path = $path;

$twig->addGlobal('path', $url_path);
$twig->addGlobal('users', $users);

/*
 * Custom order for views
 */
foreach ( $viewsOrder as $view ) {
    $jsonFile = $File->getPathByFileName($list, $view);
    $views[$view] = $jsonFile;
}

//header
$html = $twig->render('header.twig');

/**
 * Compile every json to views from /src/views
 */
foreach ($views as $jsonFileName => $jsonFile) {

    if ( file_exists(getcwd() . "/src/views/$jsonFileName.twig") ) {

        switch ($jsonFileName) {
            case ('messages'):
                $html.=$twig->render("$jsonFileName.twig", ['data' => $messages_updated]);
                break;
            case ('comments'):
                $html.=$twig->render("$jsonFileName.twig", ['data' => $comments_list, 'frequency' => $comments_frequency]);
                break;
            case ('likes'):
                $html.=$twig->render("$jsonFileName.twig", ['data' => $likes_list, 'frequency' => $likes_frequency]);
                break;
            default:
                $data = json_decode(file_get_contents($jsonFile), true);
                $html.=$twig->render("$jsonFileName.twig", ['data' => $data]);
                break;
        }
    }
}

//footer
$html.=$twig->render('footer.twig');

file_put_contents('result.html', $html);

echo '✨ Done! Check result.html'. PHP_EOL;