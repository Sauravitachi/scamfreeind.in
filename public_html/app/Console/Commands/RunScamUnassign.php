<?php

namespace App\Console\Commands;

use App\Actions\Scams\UnassignScamsWithStatus;
use Illuminate\Console\Command;

class RunScamUnassign extends Command
{
    protected $signature = 'scams:unassign';

    protected $description = 'Manually run the scam unassign action (same as the nightly scheduler)';

    public function handle(): int
    {
        $this->info('Running UnassignScamsWithStatus...');

        (new UnassignScamsWithStatus())->handle();

        $this->info('Done.');

        return 0;
    }
}
