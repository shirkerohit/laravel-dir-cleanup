<?php

namespace Tests\Unit;

use Orchestra\Testbench\TestCase;
use Rohits\Src\Cleanup;

class CleanupClassTest extends TestCase
{
    var $slug = "cleanup-package-test-data";
    var $root = null;
    var $directories = [];
    var $depth = "*";
    var $extensions = [];
    var $log = true;
    var $logDirectory = null;
    var $logFile = null;
    var $app = null;

    // used by tests setup.
    var $testDirs =  ["folder1"];
    var $testFiles = ["test.csv", "test.txt"];
    var $nestingLevel = 4;

    public function __construct()
    {
        parent::__construct();
        $this->root = __DIR__ . DIRECTORY_SEPARATOR . $this->slug;
        $this->directories = $this->testDirs;
        $this->depth = "*";
        $this->extensions = ["txt", "csv"];
        $this->log = true;
        $this->logDirectory = "log_directory";
        $this->logFile = "cleanup_log.txt";
    }

    protected function setUp(): void
    {
        parent::setUp();

        // create test directory root.
        if ((!file_exists($this->root)) && (!is_dir($this->root))) {
            @mkdir($this->root);
        }
        return;
    }

    public function cleanup()
    {
        $level = 1;
        $dirList = [];
        foreach ($this->testDirs as $dir) {
            $dirPath = $this->root . DIRECTORY_SEPARATOR . $dir;
            while ($level <= $this->nestingLevel) {
                $this->deleteFilesAndDir($dirPath);
                array_push($dirList, $dirPath);
                $dirPath = $dirPath . DIRECTORY_SEPARATOR . $dir;
                $level++;
            }
            $level = 1;
        }

        // clean directories.
        array_map(function ($dir) {
            rmdir($dir);
        }, array_reverse($dirList));
    }

    public function deleteFilesAndDir($path)
    {
        foreach ($this->testFiles as $file) {
            $filePath = $path . DIRECTORY_SEPARATOR . $file;
            file_exists($filePath) ? unlink($filePath) : "";
        }
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->app = $app;

        $app['config']->set('cleanup.root', $this->root);
        $app['config']->set('cleanup.directories', $this->directories);
        $app['config']->set('cleanup.level', $this->depth);
        $app['config']->set('cleanup.extensions', $this->extensions);
        $app['config']->set('cleanup.log', $this->log);
        $app['config']->set('cleanup.logDirectory', $this->logDirectory);
        $app['config']->set('cleanup.logFileName', $this->logFile);
    }

    public function createDirs($dirs = [])
    {
        $level = $this->nestingLevel;

        foreach ($dirs as $dir) {
            $dirPath = $this->root;
            $dirPath = $this->createTestDirAndTestFiles($dirPath, $dir);
            while ($level > 1) {
                $dirPath = $this->createTestDirAndTestFiles($dirPath, $dir);
                $level--;
            }
            $level = $this->nestingLevel;
        }
    }

    public function createTestDirAndTestFiles($path, $dirname)
    {
        $path = $path . DIRECTORY_SEPARATOR . $dirname;
        !file_exists($path) || !is_dir($path) ?  @mkdir($path) : "";

        foreach ($this->testFiles as $file) {
            fopen($path . DIRECTORY_SEPARATOR . $file, "a");
        }

        return $path;
    }

    /**
     * Tests begin here.
     */

    public function test_it_loads_config_and_does_basic_setup()
    {
        $this->assertNotEmpty(config('cleanup'));
        $this->assertEquals(config('cleanup.root'), $this->root);
        $this->assertEquals(config('cleanup.directories'), $this->directories);
        $this->assertEquals(config('cleanup.level'), $this->depth);
        $this->assertEquals(config('cleanup.extensions'), $this->extensions);
        $this->assertEquals(config('cleanup.log'), $this->log);
        $this->assertEquals(config('cleanup.logDirectory'), $this->logDirectory);
        $this->assertEquals(config('cleanup.logFileName'), $this->logFile);

        $obj = new Cleanup();
        $obj->cleanup();

        $this->assertDirectoryExists($this->root . DIRECTORY_SEPARATOR . $this->logDirectory);
        $this->assertFileExists($this->root . DIRECTORY_SEPARATOR . $this->logDirectory . DIRECTORY_SEPARATOR . $this->logFile);
    }

    public function test_it_delete_only_files_with_maching_extention()
    {
        $matchExtentions = ["txt"];

        $this->createDirs($this->testDirs);
        $this->app['config']->set('cleanup.directories', $this->testDirs);
        $this->app['config']->set('cleanup.level', "*");
        $this->app['config']->set('cleanup.extensions', $matchExtentions);

        $obj = new Cleanup();
        $obj->cleanup();
        // check only txt deleted
        $level = 1;
        foreach ($this->testDirs as $dir) {
            $path = config('cleanup.root') . DIRECTORY_SEPARATOR . $dir;
            while ($level  <= $this->nestingLevel) {
                $this->assertFileDoesNotExist($path . DIRECTORY_SEPARATOR . "test.txt");
                $this->assertFileExists($path . DIRECTORY_SEPARATOR . "test.csv");
                $path = $path . DIRECTORY_SEPARATOR . $dir;
                $level++;
            }
            $level = 1;
        }
    }

    public function test_it_only_delete_specified_level()
    {
        $nestLevelTest = 2;
        $testExtensions = ["txt"];

        $this->app['config']->set('cleanup.directories', $this->testDirs);
        $this->app['config']->set('cleanup.level', $nestLevelTest);
        $this->app['config']->set('cleanup.extensions', $testExtensions);
        $this->createDirs($this->testDirs);
        $obj = new Cleanup();
        $obj->cleanup();

        $level = 1;
        $nestLevel = config('cleanup.level');
        foreach ($this->testDirs as $dir) {
            $path = config('cleanup.root') . DIRECTORY_SEPARATOR . $dir;
            while ($level <= $this->nestingLevel) {
                if ($level <= $nestLevel) {
                    $this->assertFileDoesNotExist($path . DIRECTORY_SEPARATOR . "test.txt");
                } else {
                    $this->assertFileExists($path . DIRECTORY_SEPARATOR . "test.txt");
                }
                $this->assertFileExists($path . DIRECTORY_SEPARATOR . "test.csv");
                $path = $path . DIRECTORY_SEPARATOR . $dir;
                $level++;
            }
            $level = 1;
        }
    }

    public function test_it_clean_dummy_folders()
    {
        $this->cleanup();
        $this->assertTrue(true);
    }
}
