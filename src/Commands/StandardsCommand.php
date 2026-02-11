<?php

namespace EdStevo\Standards\Commands;

use Illuminate\Console\Command;

class StandardsCommand extends Command
{
    public $signature = 'edstevo:standards';

    public $description = 'Display EdStevo Standards information';

    public function handle(): int
    {
        $this->info('EdStevo Standards is active!');
        $this->comment('AI Boost guidelines are available for this project.');

        return self::SUCCESS;
    }
}
