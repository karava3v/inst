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
    public $required_files = ['comments', 'connections', 'likes', 'media',
        'messages', 'profile', 'saved', 'searches', 'settings'];


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

    /**
     * @return array
     */
    public function getRequiredFiles() {
        return $this->required_files;
    }

    /**
     * @param $path string to dir with json files (unziped dump from instagram)
     * @return array
     */
    public function getListOfJsonFiles( $path ) {
        $result = glob($path . "/*.json");

        return $result;
    }

    /**
     * Checks the file list for all required files
     * @param $filesList array array with paths of json files
     * @return bool
     * @throws \Error if not all files are found
     */

    public function checkForRequiredFiles( $filesList ) {
        $requiredFiles = $this->getRequiredFiles();
        $requiredFilesCount = count($requiredFiles);
        $jsonList = array();
        $filesCount = 0;

        foreach ($filesList as $file) {

            if ( $name = $this->extractNameFromPath( $file ) ) {

                if ( in_array( $name, $requiredFiles ) ) {
                    $filesCount++;
                    $jsonList[] = $name;
                }

            }
        }

        if ( $filesCount == $requiredFilesCount )
            return true;

        $requiredFilesNotFounded = implode(', ', array_diff($requiredFiles, $jsonList) );

        throw new \Error( "Files {$requiredFilesNotFounded} not found." );
    }

    /**
     * Extract filename from json path
     *
     * @param $path string path to file
     *
     * @return bool|string
     */
    public function extractNameFromPath( $path ) {
        if ( preg_match('/.+\/(?\'name\'.*)\.json$/', $path,  $jsonFile) )
            return $jsonFile['name'];

        return false;
    }

    /**
     * Search for file.json in array with jsons
     * @param $list array list of json files
     * @param $term string term for search
     * @return string|bool file.json path or false if it don't exists
     */

    public function getPathByFileName( $list, $term ) {
          $matches = preg_grep (' /'. $term .'.json$/', $list);
          $values = array_values($matches);

          $file = array_shift($values);

          return ($file) ? $file : false;
    }
}
