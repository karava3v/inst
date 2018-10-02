<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//
//$params = array(
//    ''   => 'help',
//    'z:' => 'zip:',
//    'u:' => 'user:'
//);
//
//$errors = [];
//$zip = null;
//$user = null;
//
//$options = getopt(implode('', array_keys($params)), $params);
//
//if (isset($options['zip']) || isset($options['z'])) {
//    $zip = isset($options['zip']) ? $options['zip'] : $options['z'];
//} else {
//    $errors[] = 'zip required';
//}
//
//if (isset($options['user']) || isset($options['u'])) {
//    $user = isset($options['user']) ? $options['user'] : $options['u'];
//} else {
//    $errors[] = 'user required';
//}
//
//if (isset($options['help']) || count($errors)) {
//    $help = "
//    usage: php instagram.php [--help] [-u|--user=instagram_nickname] [-z|--zip=zip_name.zip]
//
//    Options:
//                --help      Show this message
//            -z  --zip       Zip filename
//            -u  --user      Instagram username
//    Example:
//            php instagram.php --zip=instagram_20180823.zip --user=nekaravaev
//    ";
//
//    if ($errors) {
//        $help .= 'Errors:   ' . PHP_EOL . implode(", ", $errors) . PHP_EOL;
//    }
//
//    die($help);
//}

$zip = $_GET['zip'];
$user = $_GET['user'];

require_once "vendor/autoload.php";
use instagram\helpers\File;
use instagram\helpers\Request;
use instagram\models\Profile;

const COMMENT_DATE = 0;
const COMMENT_TEXT = 1;
const COMMENT_TO = 2;

$users = [];

$File = new File();

//$zipPath = getcwd() . "/$zip";
//
//$ext = pathinfo($zipPath, PATHINFO_EXTENSION);
//$filename = explode('.', $zip);
//
//if( $File->checkExtension($ext) && $File->checkType($zipPath) ){
//    $path = getcwd() . '/tmp/'. $filename[0];
//
//    if (!is_dir($path)) {
//        $File->createDir($path);
//    }
//
//    try {
//        $path = $File->extractZip($zipPath, $path);
//    } catch (\ErrorException $exception) {
//        echo $exception->getMessage();
//    }
//
//} else {
//   throw new \Exception('Invalid zip');
//}


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

$path = '/Users/karavaev/works/me/instagram-viewer/tmp/nekaravaev_20180823';

$list = $File->getListOfJsonFiles($path);

try {
    $File->checkForRequiredFiles( $list );
} catch (\Error $e) {
    die( $e->getMessage() );
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


/**
 * Work with messages, getting user's DM interlocutors for improve appearance
 */

$messages = $File->getPathByFileName($list, 'messages');

if ( $messages ) {
    $messages_updated = [];
    $Request          = new Request();

    $messages = json_decode( file_get_contents( $messages ), true );

    foreach ($messages as $chat) {

        $participants = [];

        foreach ( $chat['participants']  as $participant) {

            if ($participant !== $Profile->username) {

                if (array_key_exists($participant, $users)) {

                    $participants[$participant] = $users[$participant];

                } else {

                    $Participant = new Profile();
                    try {
                        $participant_data = $Participant->setRequest( $Request )->getFromInstagram($participant);
                        $Participant->Fill( $participant_data );

                        //TODO: rename variables so PARTICIPANTS PARTICIPANTS PARTICIPANTS everywhere
                        $users[$participant]        = $Participant;
                        $participants[$participant] = $Participant;

                    } catch (Exception $e) {
//                        echo $e->getMessage();
                        $participants[$participant] = [];
                    }
                }

            } else {
                $participants[$participant] = $Profile;
            }
        }

        $messages_updated['dialogs'][] = [
            'participants' => $participants,
            'conversation' => $chat['conversation']
        ];
    }
    $messages_updated['profiles'] = $users;
}

/**
 * Comments
 */


$comments = $File->getPathByFileName($list, 'comments');

if ( $comments ) {

    $comments_frequency = [];

    if ( !isset( $Request ) )
        $Request = new Request();

    $comments = json_decode( file_get_contents( $comments ), true );

    foreach ( $comments as $commentsCategory => $commentsList ) {

        foreach ( $commentsList as $commentID => $comment ) {

            if ($comment[COMMENT_TO] !== $Profile->username) {

                //count frequency
                if ( isset( $comments_frequency[$comment[COMMENT_TO]] ) ) {
                    $comments_frequency[$comment[COMMENT_TO]]++;
                } else {
                    $comments_frequency[$comment[COMMENT_TO]] = 1;
                }

                if (array_key_exists($comment[COMMENT_TO], $users)) {

                    $comments[$commentsCategory][$commentID][COMMENT_TO] = $users[$comment[COMMENT_TO]];

                } else {

                    $RecipientProfile = new Profile();

                    try {
                        $recipient_data = $RecipientProfile->setRequest( $Request )->getFromInstagram($comment[COMMENT_TO]);
                        $RecipientProfile->Fill( $recipient_data );

                        //TODO: rename variables so PARTICIPANTS PARTICIPANTS PARTICIPANTS everywhere
                        $users[$comment[COMMENT_TO]] = $RecipientProfile;
                        $comments[$commentsCategory][$commentID][COMMENT_TO]  = $RecipientProfile;

                    } catch (Exception $e) {
//                        echo $e->getMessage();
                        $comments[$commentsCategory][$commentID][COMMENT_TO] = [];
                    }
                }

            } else {
                $comments[$commentsCategory][$commentID][COMMENT_TO] = $Profile;
            }

        }

    }

    arsort($comments_frequency);
    $comments_frequency = array_slice($comments_frequency, 0, 10);

    $comments = [
        'list' => $comments,
        'frequency' => $comments_frequency
    ];
}

$t = $comments;


/*
* for resolving path to static
*/
$url_path = str_replace(getcwd(), '', $path);

$twig->addGlobal('path', $url_path);

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
                echo $twig->render("$jsonFileName.twig", ['data' => $comments]);
                break;
            default:
                echo $twig->render("$jsonFileName.twig", ['data' => json_decode(file_get_contents($jsonFile))]);
                break;
        }
    }
}

//footer
echo $twig->render('footer.twig');

