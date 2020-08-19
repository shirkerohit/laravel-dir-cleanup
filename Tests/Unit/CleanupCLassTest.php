<?php

namespace Tests\Unit;

use Mockery;
use Orchestra\Testbench\TestCase;
use Rohits\Src\Cleanup;

class CleanupClassTest extends TestCase
{
    var $root = __DIR__ . "\\..\\TestData";
    var $directories = ["Test1", "Test2"];
    var $depth = "*";
    var $extentions = ["csv"];
    var $log = true;
    var $logDirectory = "Test";
    var $logFile = "log_file.txt";
    var $app = null;

    protected function getEnvironmentSetUp($app)
    {
        $this->app = $app;

        $app['config']->set('cleanup.root', $this->root);
        $app['config']->set('cleanup.directories', $this->directories);
        $app['config']->set('cleanup.level', $this->depth);
        $app['config']->set('cleanup.extentions', $this->extentions);
        $app['config']->set('cleanup.log', $this->log);
        $app['config']->set('cleanup.logDirectory', $this->logDirectory);
        $app['config']->set('cleanup.logFileName', $this->logFile);
    }

    public function test_config_loads()
    {
        $obj = new Cleanup();

        $this->assertNotEmpty(config('cleanup'));
        $this->assertEquals(config('cleanup.root'), $this->root);
        $this->assertEquals(config('cleanup.directories'), $this->directories);
        $this->assertEquals(config('cleanup.level'), $this->depth);
        $this->assertEquals(config('cleanup.extentions'), $this->extentions);
        $this->assertEquals(config('cleanup.log'), $this->log);
        $this->assertEquals(config('cleanup.logDirectory'), $this->logDirectory);
        $this->assertEquals(config('cleanup.logFileName'), $this->logFile);

        $obj->cleanup();

        $this->assertDirectoryExists(__DIR__ . "\\..\\TestData\\test");
        $this->assertFileExists(__DIR__ . "\\..\\TestData\\test\\log_file.txt");
    }

    public function createDirs($dirs = [])
    {
        foreach ($dirs as $dir) {
            @mkdir($this->root . DIRECTORY_SEPARATOR . $dir);
            fopen($this->root . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'test.txt', "a");
            fopen($this->root . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'test.csv', "a");

            @mkdir($this->root . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $dir);
            fopen($this->root . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'test.txt', "a");
            fopen($this->root . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'test.csv', "a");
        }
    }

    public function test_it_delete_specified_matching_files()
    {
        $dirs = ["folder1", "folder2", "folder3"];
        $this->createDirs($dirs);
        $this->app['config']->set('cleanup.directories', $dirs);
        $this->app['config']->set('cleanup.level', "*");
        $this->app['config']->set('cleanup.extentions', ['txt']);
        $obj = new Cleanup();
        $obj->cleanup();
        // check only txt deleted
        $this->assertFileDoesNotExist(config('cleanup.root') . DIRECTORY_SEPARATOR . $dirs[0] . DIRECTORY_SEPARATOR . "test.txt");
        $this->assertFileExists(config('cleanup.root') . DIRECTORY_SEPARATOR . $dirs[0] . DIRECTORY_SEPARATOR . "test.csv");
    }

    public function test_it_delete_specified_level()
    {
        // test depth all
        $dirs = ["folder1", "folder2", "folder3"];
        $this->createDirs($dirs);
        $this->app['config']->set('cleanup.directories', $dirs);
        $this->app['config']->set('cleanup.level', "*");
        $this->app['config']->set('cleanup.extentions', ['txt', 'csv']);
        $obj = new Cleanup();
        $obj->cleanup();

        $this->assertFileDoesNotExist(config('cleanup.root') . DIRECTORY_SEPARATOR . $dirs[0] . DIRECTORY_SEPARATOR . "test.txt");
        $this->assertFileDoesNotExist(config('cleanup.root') . DIRECTORY_SEPARATOR . $dirs[0] . DIRECTORY_SEPARATOR . $dirs[0] . DIRECTORY_SEPARATOR . "test.txt");

        // test single level.
        $this->createDirs($dirs);
        $this->app['config']->set('cleanup.level', 1);
        $obj = new Cleanup();
        $obj->cleanup();
        $this->assertFileExists(config('cleanup.root') . DIRECTORY_SEPARATOR . $dirs[0] . DIRECTORY_SEPARATOR . $dirs[0] . DIRECTORY_SEPARATOR . "test.txt");

        // test level 2
        $this->createDirs($dirs);
        $this->app['config']->set('cleanup.level', 2);
        $this->app['config']->set('cleanup.extentions', ["csv"]);
        $obj = new Cleanup();
        $obj->cleanup();
        $this->assertFileDoesNotExist(config('cleanup.root') . DIRECTORY_SEPARATOR . $dirs[0] . DIRECTORY_SEPARATOR . $dirs[0] . DIRECTORY_SEPARATOR . "test.csv");
        $this->assertFileExists(config('cleanup.root') . DIRECTORY_SEPARATOR . $dirs[0] . DIRECTORY_SEPARATOR . $dirs[0] . DIRECTORY_SEPARATOR . "test.txt");

        // test level that does not exits.
        $this->createDirs($dirs);
        $this->app['config']->set('cleanup.level', 10);
        $this->app['config']->set('cleanup.extentions', ["txt", "csv"]);
        $obj = new Cleanup();
        $obj->cleanup();
        $this->assertFileDoesNotExist(config('cleanup.root') . DIRECTORY_SEPARATOR . $dirs[0] . DIRECTORY_SEPARATOR . $dirs[0] . DIRECTORY_SEPARATOR . "test.txt");
    }
}
