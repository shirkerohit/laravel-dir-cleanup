<?php

namespace Rohits\Src;

use Rohits\Src\Contracts\CleanupInterface;
use Exception;

class Cleanup implements CleanupInterface
{
    protected $root = "null";

    protected $directories = [];

    protected $fileExtensions = [];

    protected $depth = "*";

    protected $log = true;

    protected $logger = null;

    protected $logDirectory = null;

    protected $logFileName = null;

    protected $logDirectoryFilePath = null;

    public function __construct()
    {
        $this->setup();
    }

    private function setup()
    {
        $this->loadConfig()
            ->setupLogs();
    }

    private function loadConfig()
    {
        $this->root = config("cleanup.root", null);
        $this->directories = config("cleanup.directories", []);
        $this->fileExtensions = config("cleanup.extensions", []);
        $this->depth = config("cleanup.level", "*");
        $this->log = config("cleanup.log", true);
        $this->logDirectory = config("cleanup.logDirectory", null);
        $this->logFileName = config("cleanup.logFileName", "cleanup_log.txt");
        return $this;
    }

    private function setupLogs()
    {
        if ($this->log) {
            $this->logger = new Logger($this->root, $this->logDirectory, $this->logFileName);
        }
        return $this;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function getDirectories()
    {
        return $this->directories;
    }

    public function getDepth()
    {
        return $this->depth;
    }

    public function getFileExtensionPattern()
    {
        return $this->fileExtensions;
    }

    public function cleanup()
    {
        $this->writeLog("Started: " . date_format(now(), "Y-m-d H:i:s"));

        $this->checkIfRootExists()
            ->iterateBaseDirectories();

        $this->writeLog("Ended: " . date_format(now(), "Y-m-d H:i:s"));
    }

    private function checkIfRootExists()
    {
        if (!$this->isDirExist($this->root)) {
            throw new Exception("No such {$this->root} root directory found!");
        }
        return $this;
    }

    private function getSubDirFullPath($directory)
    {
        $path = $this->root . DIRECTORY_SEPARATOR . $directory;
        if (file_exists($path)) {
            return $path;
        }
        return null;
    }

    private function isDirExist($directory)
    {
        if ((!file_exists($directory)) || (!is_dir($directory))) {
            return false;
        }
        return true;
    }

    private function iterateBaseDirectories()
    {
        foreach ($this->directories as $directory) {
            $subDirPath = $this->getSubDirFullPath($directory);
            if ($subDirPath != null) {
                $files = $this->iterateSubDirContentTillDepth($subDirPath, $this->depth);
                $this->deleteFiles($files);
            }
        }
        return $this;
    }

    private function deleteFiles($files = [])
    {
        foreach ($files as $file) {
            $this->log($file);
            unlink($file);
        }
        return $this;
    }

    private function iterateSubDirContentTillDepth($subDirPath, $times = "*")
    {
        $files = [];
        $directoryContent = $this->getDirectoryFiles($subDirPath);

        if (empty($directoryContent) || $times === 0) {
            return $files;
        }

        foreach ($directoryContent as $leaf) {
            if (is_file($leaf) && $this->matchFilePattern($leaf)) {
                array_push($files, $leaf);
            } else if (is_dir($leaf)) {
                $times = $times === "*" ? "*" : (int)$times - 1;
                $files = array_merge($this->iterateSubDirContentTillDepth($leaf, $times), $files);
            }
        }
        return $files;
    }

    private function getDirectoryFiles($directory)
    {
        $files = [];
        $directoryElement = scandir($directory);
        foreach ($directoryElement as $element) {
            if ($element != "." && $element != "..") {
                array_push($files, $directory . DIRECTORY_SEPARATOR . $element);
            }
        }

        return $files;
    }

    private function matchFilePattern($file)
    {
        $fileExtension = pathinfo(basename($file), PATHINFO_EXTENSION);
        if (in_array($fileExtension, $this->getFileExtensionPattern())) {
            return true;
        }
        return false;
    }

    private function log($directoryPath)
    {
        $this->writeLog("Deleting: " . $directoryPath);
        return;
    }

    private function writeLog($message)
    {
        if ($this->log) {
            $this->logger->write($message);
        }
        return;
    }

    public function getLogPath()
    {
        if ($this->log) {
            return $this->logger->getLogFilePath();
        }
        return "NA";
    }
}
