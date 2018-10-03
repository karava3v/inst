<?php

require_once "vendor/autoload.php";
use instagram\helpers\File;
use instagram\helpers\Request;
use instagram\models\Profile;

const COMMENT_DATE = 0;
const COMMENT_TEXT = 1;
const COMMENT_TO = 2;

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
    'connections',
    'likes',
    'comments',
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
} else
    throw new Error('No profile found');

$users[$Profile->username] = $Profile;


/**
 * Work with messages, getting user's DM interlocutors for improve appearance
 */

$messages = $File->getPathByFileName($list, 'messages');

if ( $messages ) {
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

$new_users = array_unique(array_merge($messages_new_users, $comments_new_users, $likes_new_users));

if ( !empty($new_users) ) {
    foreach ( $new_users as $user ) {

        $Participant = new Profile();

        try {
            $participant_data = $Participant->setRequest( $Request )->getFromInstagram($user);
            $Participant->Fill( $participant_data );

            $users[$user]  = $Participant;

        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}

/*
* for resolving path to static
*/
$url_path = str_replace(getcwd(), '', $path);

$twig->addGlobal('path', $url_path);
$twig->addGlobal('users', $users);

//header
echo $twig->render('header.twig');


/*
 * Custom order for views
 */
foreach ( $viewsOrder as $view ) {
    $jsonFile = $File->getPathByFileName($list, $view);
    $views[$view] = $jsonFile;
}

/**
 * Compile every json to views from /src/views
 */
foreach ($views as $jsonFileName => $jsonFile) {

    if ( file_exists(getcwd() . "/src/views/$jsonFileName.twig") ) {

        switch ($jsonFileName) {
            case ('messages'):
                echo $twig->render("$jsonFileName.twig", ['data' => $messages_updated]);
                break;
            case ('comments'):
                echo $twig->render("$jsonFileName.twig", ['data' => $comments_list, 'frequency' => $comments_frequency]);
                break;
            case ('likes'):
                echo $twig->render("$jsonFileName.twig", ['data' => $likes_list, 'frequency' => $likes_frequency]);
                break;
            default:
                echo $twig->render("$jsonFileName.twig", ['data' => json_decode(file_get_contents($jsonFile))]);
                break;
        }
    }
}

//footer
echo $twig->render('footer.twig');