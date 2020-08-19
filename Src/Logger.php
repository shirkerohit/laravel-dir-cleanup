<?php

namespace Rohits\Src;

use Rohits\Src\Contracts\LoggerInterface;

class Logger implements LoggerInterface
{
    protected $root = null;

    protected $directory = null;

    protected $logFilePath = null;

    protected $fileName = "clean_up_log.txt";

    public function __construct($root, $directory, $file)
    {
        $this->root = $root;
        $this->directory = $directory;
        $this->fileName = $file;
        $this->setup();
    }

    public function setup()
    {
        $this->createLogDir()
            ->createLogFile();
    }

    private function createLogDir()
    {
        $this->directory = $this->root . DIRECTORY_SEPARATOR . $this->directory;
        if ((!file_exists($this->directory)) || !is_dir($this->directory)) {
            try {
                @mkdir($this->directory);
            } catch (Exception $err) {
                $this->directory = getcwd();
            }
        }
        return $this;
    }

    private function createLogFile()
    {
        if (!file_exists($this->getLogFilePath())) {
            $handle = fopen($this->getLogFilePath(), "x+");
            fclose($handle);
        }
        return $this;
    }

    public function getLogFilePath()
    {
        $this->logFilePath = $this->directory . DIRECTORY_SEPARATOR . $this->fileName;
        return $this->logFilePath;
    }

    public function write($message)
    {
        file_put_contents(
            $this->getLogFilePath(),
            $message . "\n",
            FILE_APPEND
        );
    }
}
