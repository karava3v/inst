<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "vendor/autoload.php";
use instagram\helpers\File;

if (isset($_FILES['file'])) {
    session_start();
    $File = new File();

    $filename = $_FILES['file']['name'];
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $filename = explode('.', $filename);

    $tmp_name = $_FILES['file']['tmp_name'];

    if( $File->checkExtension($ext) && $File->checkType($tmp_name) ){
        $path = getcwd() . '/tmp/'. $filename[0];

        if (!is_dir($path)) {
            $File->createDir($path);
        }

        try {
           $path = $File->extractZip($tmp_name, $path);
           $_SESSION['path'] = $path;
            header("Location: /process.php");
            die();
        } catch (\ErrorException $exception) {
            echo $exception->getMessage();
        }
    } else {
        echo 'Invalid file';
    }
}