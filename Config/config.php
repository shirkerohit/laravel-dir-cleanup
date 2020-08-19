<?php

return [

    /**
     * Define the root of directory you want to delete the files from.
     * If you have multiple roots, then you may specify the their parent as root.
     */
    "root" => null,

    /**
     * List of directories to clean-up.
     */
    "directories" => [],

    /**
     * Depth of directories to iterate.
     * By default we iterate till the last leaf.
     */
    "level" => "*",

    /**
     * List of extentions of files to be deleted. eg. csv;
     */
    "extentions" => [],

    /**
     * Specify if you want to log the deleted files.
     * By default we disabled logs as Its important that,
     * you configure the log directory else logs will fill up default directory.
     */
    "log" => false,

    /**
     * Specify a custom log location.
     */
    "logDirectory" => null,

    /**
     * Specify a custom log file name.
     */
    "logFileName" => null,
];
