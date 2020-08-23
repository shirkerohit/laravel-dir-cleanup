# laravel-dir-cleanup
Simple package to delete unwanted files from list of directories.

[![Latest Stable Version](https://poser.pugx.org/rohits/laravel-dir-cleanup/v)](//packagist.org/packages/rohits/laravel-dir-cleanup) [![Total Downloads](https://poser.pugx.org/rohits/laravel-dir-cleanup/downloads)](//packagist.org/packages/rohits/laravel-dir-cleanup) [![Latest Unstable Version](https://poser.pugx.org/rohits/laravel-dir-cleanup/v/unstable)](//packagist.org/packages/rohits/laravel-dir-cleanup) [![License](https://poser.pugx.org/rohits/laravel-dir-cleanup/license)](//packagist.org/packages/rohits/laravel-dir-cleanup)

# Installation :

## Composer :

`composer require rohits/laravel-dir-cleanup`


## Usage :

  ### Register service provider :
Open your app.config file and add following Line.

```Rohits\Src\CleanUpserviceProvider::class ```

  ### Publish config File :

`php artisan vendor publish`

- choose appropriate provider here its  `Rohits\Src\CleanUpserviceProvider` & it should publish a file named `cleanup.php` under your config directory.

### Update config file :
- root : Root folder under which you want to delete the files.
- directories : Specify the list of directories you want the package to iterate to delete files.
- level : Specify the depth of directory to iterate. By default all subdirectories will iterated till the last leaf node.
- extensions : Specify the extension to match for deleting files eg. csv
 <strong> Simply specify extension (eg. txt) without prefix (.) </strong>
- log : Specify if you want to log the files deleted. This is intentionally kept disabled as you need to specify the directory of log file rather the flooding the default one.
- logDirectory : Name of the directory you want to keep the logs.
- logFileName : Name of the log file if any. By default a file with name cleanup_log.txt will be used.

### Configure the cleanup :
- The package ships with the command that help you schedule it when you want to run the cleanup. Just schedule the command as you would do a normal command.

```
     /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('cleanup-dirs')
            ->everyHour();
    }

```

- You can also manually run the cleanup CRON.

```php artisan cleanup-dirs```

<strong> (Note : Please make sure you run `php artisan config:clear` before you run the command.) </strong>


### Tests :
Make sure php is in your path and simply run below command.

``vendor/bin/phpunit``

### Issues:

Please report them @ rohit97on@gmail.com

### License :

The MIT License.
