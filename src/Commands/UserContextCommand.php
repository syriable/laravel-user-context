<?php

namespace Syriable\UserContext\Commands;

use Illuminate\Console\Command;

class UserContextCommand extends Command
{
    public $signature = 'laravel-user-context';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
