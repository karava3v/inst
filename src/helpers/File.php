<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 23.08.18
 * Time: 7:52 PM
 */

namespace instagram\helpers;

class File
{
    public $accepted_types = ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed'];
    public $accepted_extensions = ['zip'];


    /**
     * @param string $path path for file
     * @return bool is file understanble on not
     */

    public function checkType($path)
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        if (false === $ext = array_search(
                $finfo->file($path),
                $this->accepted_types,
                true
            )) {
            return false;
        }

        return true;
    }

    /**
     * @param string $extension extension of file
     * @return bool is file understanble on not
     */

    public function checkExtension($extension)
    {
        return in_array($extension, $this->accepted_extensions);
    }

    /**
     * @param string $file file path with zip
     * @param string $path path for extracted files
     * @return string extracted zip path
     * @throws \ErrorException if can't open zip file
     */

    public function extractZip($file, $path)
    {
        $Zip = new \ZipArchive($file);

        if ( $Zip->open($file) ) {

            $Zip->extractTo( $path );
            $Zip->close();
            return $path;
        } else {
            throw new \ErrorException('Can\'t unzip file');
        }
    }

    /**
     * @param $path string path for new dir
     * @return bool
     */
    public function createDir($path) {
        return mkdir($path);
    }

    /**
     * Recursively remove dir
     * @param $dir string path for dir
     * @return void
     */

    public function removeDir($dir)
    {
        foreach (scandir($dir) as $file) {
            if ('.' === $file || '..' === $file) {
                continue;
            }
            if (is_dir("$dir/$file")) {
                $this->removeDir("$dir/$file");
            } else {
                unlink("$dir/$file");
            }
        }

        rmdir($dir);
    }
}
