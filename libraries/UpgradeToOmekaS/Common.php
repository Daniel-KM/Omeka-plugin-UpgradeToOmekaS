<?php

// Make this class compliant outside of Omeka.
if (!function_exists('__')) {
    function __($msgid)
    {
        if (is_array($msgid)) {
            $string = ($msgid[2] === 1) ? $msgid[0] : $msgid[1];
        } else {
            $string = $msgid;
        }

        $args = func_get_args();
        array_shift($args);

        if (!empty($args)) {
            return vsprintf($string, $args);
        }

        return $string;
    }
}

/**
 * UpgradeToOmekaS_Common class
 *
 * @todo This class is a copy of another plugin, so some methods may be removed.
 *
 * @package UpgradeToOmekaS
 */
class UpgradeToOmekaS_Common
{
    /**
     * Determines if a directory is empty.
     *
     * @link https://stackoverflow.com/questions/7497733/how-can-use-php-to-check-if-a-directory-is-empty#7497848
     *
     * @param string $dir
     * @return null|boolean
     */
    public static function isDirEmpty($dir)
    {
        if (empty($dir) || !file_exists($dir) || !is_dir($dir) || !is_readable($dir)) {
            return null;
        }
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != '.' && $entry != '..') {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the size of a directory.
     *
     * @link https://stackoverflow.com/questions/478121/php-get-directory-size#21409562
     *
     * @param string $path
     * @return number
     */
    public static function getDirectorySize($dir)
    {
        if (empty($dir) || !file_exists($dir) || !is_dir($dir) || !is_readable($dir)) {
            return null;
        }
        $bytestotal = 0;
        $path = realpath($dir);
        if($path!==false){
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object){
                $bytestotal += $object->getSize();
            }
        }
        return $bytestotal;
    }

    /**
     * Determines the number of files of a directory.
     *
     * @link https://stackoverflow.com/questions/12801370/count-how-many-files-in-directory-php
     *
     * @param string $dir
     * @return integer|null
     */
    public static function countFilesInDir($dir)
    {
        if (empty($dir) || !file_exists($dir) || !is_dir($dir) || !is_readable($dir)) {
            return null;
        }
        $fi = new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS);
        $total = iterator_count($fi);
        return $total;
    }

    /**
     * Determines if a directory contains symbolic links.
     *
     * @param string $dir
     * @return boolean|null
     */
    public static function containsSymlinks($dir)
    {
        if (empty($dir) || !file_exists($dir) || !is_dir($dir) || !is_readable($dir)) {
            return null;
        }
        $recDirIterator = new recursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS);
        $iterator = new recursiveIteratorIterator($recDirIterator);
        while ($iterator->valid()) {
            if ($iterator->isLink()) {
                return true;
            }
            $iterator->next();
        }
        return false;
    }

    /**
     * List files in a directory, not recursively, and without subdirs.
     *
     * @param string $dir
     * @return array
     */
    public static function listFilesInDir($dir)
    {
        if (empty($dir) || !file_exists($dir) || !is_dir($dir) || !is_readable($dir)) {
            return;
        }
        return array_filter(scandir($dir), function($file) use ($dir) {
            return is_file($dir . DIRECTORY_SEPARATOR . $file);
        });
    }

    /**
     * List directories in a directory, not recursively.
     *
     * @param string $dir
     * @return array
     */
    public static function listDirsInDir($dir)
    {
        if (empty($dir) || !file_exists($dir) || !is_dir($dir) || !is_readable($dir)) {
            return;
        }
        return array_filter(array_diff(scandir($dir), array('.', '..')), function($file) use ($dir) {
            return is_dir($dir . DIRECTORY_SEPARATOR . $file);
        });
    }

    /**
     * Get full path of files filtered by extensions recursively in a directory.
     *
     * @param string $dir
     * @param string $extensions
     * @return array
     */
    public static function listFilesInFolder($dir, $extensions = array())
    {
        if (empty($dir) || !file_exists($dir) || !is_dir($dir) || !is_readable($dir)) {
            return null;
        }
        $regex = empty($extensions)
            ? '/^.+$/i'
            : '/^.+\.(' . implode('|', $extensions) . ')$/i';

        $Directory = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $Iterator = new RecursiveIteratorIterator($Directory);
        $Regex = new RegexIterator($Iterator, $regex, RecursiveRegexIterator::GET_MATCH);
        $files = array();
        foreach ($Regex as $file) {
            $files[] = reset($file);
        }
        sort($files);
        return $files;
    }

    /**
     * Chmod of all files recursively in a directory.
     *
     * @param string $dir
     * @param numeric $modFile
     * @param numeric $modDir
     * @return boolean
     */
    public static function chmodFolder($dir, $modFile = 0644, $modDir = 0755)
    {
        if (empty($dir) || !file_exists($dir) || !is_dir($dir) || !is_readable($dir)) {
            return false;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $item) {
            // This may be a dir.
            if ($item->isDir()) {
                $result = chmod($item, $modDir);
            }
            // This may be a file.
            elseif ($item->isFile()) {
                $result = chmod($item, $modFile);
            }
            // This may be a symbolic link or something else.
            else {
                continue;
            }
            if (!$result) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check and create a directory recursively.
     *
     * @param string $path
     * @return boolean
     */
    public static function createDir($path)
    {
        if (strlen($path) == 0) {
            return false;
        }
        if (!file_exists($path)) {
            $parent = dirname($path);
            while (!file_exists($parent) && $parent != '/') {
                $parent = dirname($parent);
            }
            if (!is_writable($parent)) {
                return false;
            }
            return mkdir($path, 0755, true);
        }
        elseif (!is_dir($path)) {
            return false;
        }

        return true;
    }

    /**
     * Copy a directory recursively.
     *
     * @link https://stackoverflow.com/questions/5707806/recursive-copy-of-directory#7775949
     *
     * @param string $source
     * @param string $destination
     * @param boolean $overwrite
     * @param array $extensionsToRename
     * @return boolean
     */
    public static function copyDir($source, $destination, $overwrite = false, $extensionsToRename = array())
    {
        if (empty($source) || empty($destination)) {
            return false;
        }

        if (!file_exists($source) || !is_dir($source) || !is_readable($source)) {
            return false;
        }

        $result = self::createDir($destination);
        if (empty($result)) {
            return false;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $item) {
            $subpath = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if (file_exists($subpath)) {
                if (is_dir($subpath)) {
                    continue;
                }
                if (!$overwrite) {
                    continue;
                }
            }
            // This may be a dir.
            if ($item->isDir()) {
                $result = mkdir($subpath, 0755, true);
            }
            // This may be a file.
            elseif ($item->isFile()) {
                $extension = $item->getExtension();
                if (isset($extensionsToRename[$extension])) {
                    $subpath = dirname($subpath)
                        . DIRECTORY_SEPARATOR . pathinfo($item->getBasename(), PATHINFO_FILENAME)
                        . '.' . $extensionsToRename[$extension];
                    if (file_exists($subpath) && !$overwrite) {
                        continue;
                    }
                }
                // Recreate the dir, that may be skipped by a symbolic link.
                self::createDir(dirname($subpath));
                $result = copy($item, $subpath);
            }
            // This may be a symbolic link or something else.
            else {
                continue;
            }
            if (!$result) {
                return false;
            }
        }

        return true;
    }

    /**
     * Copy all contents of a directory recursively.
     *
     * @param string $source
     * @param string $destination
     * @param boolean $overwrite
     * @param array $extensionsToRename
     * @return boolean
     */
    public static function copyInsideDir($source, $destination, $overwrite = false, $extensionsToRename = array())
    {
        $dirs = self::listDirsInDir($source);
        if ($dirs) {
            foreach ($dirs as $dir) {
                $result = self::copyDir(
                    $source . DIRECTORY_SEPARATOR . $dir,
                    $destination . DIRECTORY_SEPARATOR . basename($dir),
                    $overwrite,
                    $extensionsToRename);
                if (!$result) {
                    return false;
                }
            }
        }
        $files = self::listFilesInDir($source);
        if ($files) {
            foreach ($files as $file) {
                $subpath = $destination
                    . DIRECTORY_SEPARATOR . pathinfo($file, PATHINFO_BASENAME);
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                if (isset($extensionsToRename[$extension])) {
                    $subpath = $destination
                        . DIRECTORY_SEPARATOR . pathinfo($file, PATHINFO_FILENAME)
                        . '.' . $extensionsToRename[$extension];
                    if (file_exists($subpath) && !$overwrite) {
                        continue;
                    }
                }
                $result = copy($source . DIRECTORY_SEPARATOR . $file, $subpath);
                if (!$result) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Checks and removes a folder, empty or not.
     *
     * @param string $path Full path of the folder to remove.
     * @param boolean $evenNonEmpty Remove non empty folder.
     * This parameter can be used with non standard folders.
     * @return boolean.
     */
    public static function removeDir($path, $evenNonEmpty = false)
    {
        $path = realpath($path);
        if (!strlen($path) || $path == '/' || !file_exists($path)) {
            return true;
        }
        if (is_dir($path)
                && is_readable($path)
                && is_writable($path)
                && ($evenNonEmpty || count(array_diff(@scandir($path), array('.', '..'))) == 0)
            ) {
            $result = self::_rrmdir($path);
            return is_null($result) || $result;
        }
        return false;
    }

    /**
     * Removes directories recursively.
     *
     * @param string $dir Directory name.
     * @return boolean
     */
    protected static function _rrmdir($dir)
    {
        if (!file_exists($dir)
                || !is_dir($dir)
                || !is_readable($dir)
            ) {
            return;
        }
        $scandir = scandir($dir);
        if (!is_array($scandir)) {
            return;
        }
        $files = array_diff($scandir, array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                self::_rrmDir($path);
            }
            else {
                unlink($path);
            }
        }
        return @rmdir($dir);
    }

    /**
     * Process the move operation according to admin choice.
     *
     * @todo Use Omeka process like with ArchiveRepertory.
     * @see ArchiveRepertory::_moveFile()
     *
     * @param string $source
     * @param string $destination
     * @return boolean|string If not true, the message of error.
     */
    public static function moveFile($source, $destination)
    {
        if ($source === $destination) {
            return true;
        }

        if (strlen($source) == 0) {
            $message = __('The source "%s" is not defined.', $source);
            return $message;
        }

        if (strlen($destination) == 0) {
            $message = __('The destination "%s" are not defined.', $destination);
            return $message;
        }

        if (!file_exists($source)) {
            $message = __('Error during move of a file from "%s" to "%s": source does not exist.',
                $source, $destination);
            return $message;
        }

        try {
            $result = rename($source, $destination);
        } catch (Exception $e) {
            $message = __('Error during move of a file from "%s" to "%s": %s',
                $source, $destination, $e->getMessage());
            return $message;
        }

        return true;
    }

    /**
     * Determine if the uri of a file is a remote url or a local path.
     *
     * @param string $uri
     * @return boolean
     */
    public static function isRemote($uri)
    {
        $scheme = parse_url($uri, PHP_URL_SCHEME);
        return in_array($scheme, array(
            'https', 'http', 'sftp', 'ftps', 'ftp',
        ));
    }

    /**
     * Unzip a zip file into a folder.
     *
     * @uses Extension php-zip or command line unzip.
     *
     * @param string $zipFile
     * @param string $path The path where to unzip the file. It must be empty.
     * @param boolean $inside Extract the content of the first level folder
     * inside the path.
     * @return boolean True on success.
     */
    public static function extractZip($zipFile, $path, $inside = true)
    {
        // First, save the file in the temp directory, because ZipArchive and
        // unzip don't manage url.
        if (self::isRemote($zipFile)) {
            $isTempFile = true;
            $input = tempnam(sys_get_temp_dir(), basename($zipFile));
            $handle = fopen($zipFile, 'rb');
            $result = (boolean) file_put_contents($input, $handle);
            @fclose($handle);
        }
        // Check the input file.
        else {
            if (!file_exists($zipFile)) {
                return false;
            }
            $isTempFile = false;
            $input = $zipFile;
            $result = (boolean) filesize($input);
        }

        if (!empty($result)) {
            // Unzip via php-zip.
            if (class_exists('ZipArchive')) {
                $zip = new ZipArchive;
                $result = $zip->open($input);
                if ($result === true) {
                    $result = $zip->extractTo($path);
                    $zip->close();
                }
            }

            // Unzip via command line
            else {
                // Check if the zip command exists.
                try {
                    $this->executeCommand('unzip', $status, $output, $errors);
                } catch (Exception $e) {
                    $status = 1;
                }
                // A return value of 0 indicates the convert binary is working correctly.
                $result = $status == 0;
                if ($result) {
                    $command = 'unzip ' . escapeshellarg($input) . ' -d ' . escapeshellarg($path);
                    try {
                        self::executeCommand($command, $status, $output, $errors);
                    } catch (\Exception $e) {
                        $status = 1;
                    }
                    $result = $status == 0;
                }
            }
        }

        if ($isTempFile) {
            unlink($input);
        }

        if ($result && $inside) {
            $dirs = glob($path . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
            $result = count($dirs) == 1;
            if ($result) {
                // A double rename is the quickest and simplest way.
                $subDir = reset($dirs);
                $thirdDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(rtrim(strtok(substr(microtime(), 2), ' '), '0'));
                // The function rename() may not work on different file systems.
                // $result = rename($subDir, $thirdDir);
                // if ($result) {
                //     self::removeDir($path, true);
                //     $result = rename($thirdDir, $path);
                // }
                $result = self::copyDir($subDir, $thirdDir);
                if ($result) {
                    $result = self::removeDir($subDir, true);
                    if ($result) {
                        $result = self::copyInsideDir($thirdDir, $path);
                        if ($result) {
                            self::removeDir($thirdDir, true);
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Unzip a file to get the selected file content.
     *
     * @uses Extension php-zip or command line unzip.
     *
     * @param string $zipFile
     * @param string $filename The path to extract from the zip file.
     * @return string|null The content of the requested file. Null if error.
     */
    public static function extractZippedContent($zipFile, $filename)
    {
        // First, save the file in the temp directory, because ZipArchive and
        // unzip don't manage url.
        if (self::isRemote($zipfile)) {
            $isTempFile = true;
            $input = tempnam(sys_get_temp_dir(), basename($zipFile));
            $handle = fopen($zipFile, 'rb');
            $result = file_put_contents($input, $handle);
            @fclose($handle);
        }
        // Check the input file.
        else {
            if (!file_exists($zipFile)) {
                return;
            }
            $isTempFile = false;
            $input = $zipFile;
            $result = filesize($zipFile);
        }

        if (!empty($result)) {
            // Unzip via php-zip.
            if (class_exists('ZipArchive')) {
                $zip = new ZipArchive;
                if ($zip->open($input) === true) {
                    $index = $zip->locateName($filename);
                    if ($index !== false) {
                        $content = $zip->getFromIndex($index);
                    }
                    $zip->close();
                }
            }

            // Unzip via command line
            else {
                // Check if the zip command exists.
                try {
                    $this->executeCommand('unzip', $status, $output, $errors);
                } catch (Exception $e) {
                    $status = 1;
                }
                // A return value of 0 indicates the convert binary is working correctly.
                if ($status == 0) {
                    $outputFile = tempnam(sys_get_temp_dir(), basename($zipFile));
                    $command = 'unzip -p ' . escapeshellarg($input) . ' content.xml > ' . escapeshellarg($outputFile);
                    try {
                        self::executeCommand($command, $status, $output, $errors);
                    } catch (Exception $e) {
                        $status = 1;
                    }
                    if ($status == 0 && filesize($outputFile)) {
                        $content = file_get_contents($outputFile);
                    }
                    unlink($outputFile);
                }
            }
        }

        if ($isTempFile) {
            unlink($input);
        }

        return $content;
    }

    /**
     * Determine whether or not the path given is valid.
     *
     * @param string $command
     * @param string $arg Argument to use to check the command.
     * @return boolean
     */
    public static function isValidCommand($command, $arg = '--version')
    {
        if (!$command
                || !realpath($command) || is_dir($command)
                || !is_file($command) || !is_executable($command)
            ) {
            return false;
        }

        $cmd = $command . ' ' . $arg;

        self::executeCommand($cmd, $status, $output, $errors);

        // A return value of 0 indicates the convert binary is working correctly.
        return $status == 0;
    }

    /**
     * Execute a shell command without exec().
     *
     * @see Omeka_File_Derivative_Strategy_ExternalImageMagick::executeCommand()
     *
     * @param string $cmd
     * @param integer $status
     * @param string $output
     * @param array $errors
     * @throws UpgradeToOmekaS_Exception
     */
    public static function executeCommand($cmd, &$status, &$output, &$errors)
    {
        // Using proc_open() instead of exec() solves a problem where exec('convert')
        // fails with a "Permission Denied" error because the current working
        // directory cannot be set properly via exec().  Note that exec() works
        // fine when executing in the web environment but fails in CLI.
        $descriptorSpec = array(
            0 => array("pipe", "r"), //STDIN
            1 => array("pipe", "w"), //STDOUT
            2 => array("pipe", "w"), //STDERR
        );
        $proc = proc_open($cmd, $descriptorSpec, $pipes, getcwd());
        if (!is_resource($proc)) {
            throw new UpgradeToOmekaS_Exception(__('Failed to execute command: %s', $cmd));
        }
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }
        $status = proc_close($proc);
    }
}
