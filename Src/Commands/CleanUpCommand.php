<?php

namespace Rohits\Src\Commands;

use Illuminate\Console\Command;
use Rohits\Src\Cleanup;

class CleanUpCommand extends Command
{
    public $name = "Cleanup files.";

    public $signature = "cleanup-dirs";

    public $description = "Clean-up custom files.";

    public function handle()
    {
        $cleanUpObject = new Cleanup();

        $this->info("\nCleaning up directories under root : " . $cleanUpObject->getRoot());

        $cleanUpObject->cleanup();

        $this->info("\nComplete! Find Log here : " . $cleanUpObject->getLogPath());
    }
}
