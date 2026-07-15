<?php
// app/Console/Commands/UpdateCumulativePVCommand.php

namespace App\Console\Commands;

use App\Jobs\UpdateCumulativePV;
use Illuminate\Console\Command;

class UpdateCumulativePVCommand extends Command
{
    protected $signature = 'mlm:update-pv {--user=}';
    protected $description = 'Update cumulative PV for all users or specific user';

    public function handle()
    {
        $userId = $this->option('user');
        
        if ($userId) {
            $this->info("Updating cumulative PV for user ID: {$userId}");
            dispatch(new UpdateCumulativePV((int) $userId));
        } else {
            $this->info("Updating cumulative PV for all users...");
            dispatch(new UpdateCumulativePV());
        }

        $this->info('Job dispatched successfully!');
        $this->info('Run: php artisan queue:work to process');
    }
}