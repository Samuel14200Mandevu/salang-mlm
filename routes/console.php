<?php
// routes/console.php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// ============================================================
// COMMANDES EXISTANTES
// ============================================================

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ============================================================
// COMMANDES PERSONNALISÉES
// ============================================================

/**
 * Command: php artisan commissions:process
 * 
 * Process monthly commissions for the given period
 * Usage: php artisan commissions:process [--period=2024-01] [--dry-run]
 */
Artisan::command('commissions:process', function () {
    $period = $this->option('period') ?? date('Y-m', strtotime('last month'));
    $dryRun = $this->option('dry-run') ?? false;

    $this->info('Processing commissions for period: ' . $period);
    $this->info('Dry run: ' . ($dryRun ? 'YES' : 'NO'));

    try {
        $job = new \App\Jobs\ProcessMonthlyCommissions($period, $dryRun);
        $job->handle(app(\App\Services\MLM\MonthlyCommissionService::class));

        $this->info('Commissions processed successfully!');
    } catch (\Exception $e) {
        $this->error('Error: ' . $e->getMessage());
        return 1;
    }

    return 0;
})->purpose('Process monthly commissions')
    ->addOption('period', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'Period to process (format: YYYY-MM)')
    ->addOption('dry-run', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Run in dry run mode (no changes)');

/**
 * Command: php artisan ranks:update
 * 
 * Update all user ranks based on current PV
 * Usage: php artisan ranks:update [--user=1]
 */
Artisan::command('ranks:update', function () {
    $userId = $this->option('user');

    if ($userId) {
        $this->info('Updating rank for user ID: ' . $userId);
    } else {
        $this->info('Updating ranks for all users');
    }

    try {
        $job = new \App\Jobs\UpdateRanks($userId);
        $job->handle(app(\App\Services\MLM\AdvancedRankCalculator::class));

        $this->info('Ranks updated successfully!');
    } catch (\Exception $e) {
        $this->error('Error: ' . $e->getMessage());
        return 1;
    }

    return 0;
})->purpose('Update user ranks based on current PV')
    ->addOption('user', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'Specific user ID to update');

/**
 * Command: php artisan pv:calculate
 * 
 * Calculate monthly PV/BV for all users
 * Usage: php artisan pv:calculate [--period=2024-01]
 */
Artisan::command('pv:calculate', function () {
    $period = $this->option('period') ?? date('Y-m', strtotime('last month'));

    $this->info('Calculating PV/BV for period: ' . $period);

    try {
        $job = new \App\Jobs\CalculatePVBV($period);
        $job->handle();

        $this->info('PV/BV calculated successfully!');
    } catch (\Exception $e) {
        $this->error('Error: ' . $e->getMessage());
        return 1;
    }

    return 0;
})->purpose('Calculate monthly PV/BV for all users')
    ->addOption('period', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'Period to calculate (format: YYYY-MM)');

/**
 * Command: php artisan commissions:process-withdrawals
 * 
 * Process pending withdrawals
 * Usage: php artisan commissions:process-withdrawals
 */
Artisan::command('withdrawals:process', function () {
    $this->info('Processing pending withdrawals...');

    try {
        $job = new \App\Jobs\ProcessWithdrawals();
        $job->handle();

        $this->info('Withdrawals processed successfully!');
    } catch (\Exception $e) {
        $this->error('Error: ' . $e->getMessage());
        return 1;
    }

    return 0;
})->purpose('Process pending withdrawals');

/**
 * Command: php artisan commissions:remind
 * 
 * Send reminders for pending commissions
 * Usage: php artisan commissions:remind
 */
Artisan::command('commissions:remind', function () {
    $this->info('Sending commission reminders...');

    try {
        $this->call('commissions:remind');
        $this->info('Reminders sent successfully!');
    } catch (\Exception $e) {
        $this->error('Error: ' . $e->getMessage());
        return 1;
    }

    return 0;
})->purpose('Send reminders for pending commissions');

/**
 * Command: php artisan db:backup
 * 
 * Backup the database
 * Usage: php artisan db:backup [--path=/backups]
 */
Artisan::command('db:backup', function () {
    $path = $this->option('path') ?? storage_path('backups');
    $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';

    $this->info('Creating database backup...');

    try {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $fullPath = $path . '/' . $filename;

        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            env('DB_HOST'),
            env('DB_DATABASE'),
            $fullPath
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('Backup failed with code: ' . $returnCode);
        }

        $this->info('Backup created successfully: ' . $fullPath);
        $this->info('Size: ' . $this->formatSizeUnits(filesize($fullPath)));

    } catch (\Exception $e) {
        $this->error('Error: ' . $e->getMessage());
        return 1;
    }

    return 0;
})->purpose('Backup the database')
    ->addOption('path', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'Backup path', storage_path('backups'));

/**
 * Command: php artisan db:optimize
 * 
 * Optimize database tables
 * Usage: php artisan db:optimize
 */
Artisan::command('db:optimize', function () {
    $this->info('Optimizing database tables...');

    try {
        $tables = \DB::select('SHOW TABLES');

        foreach ($tables as $table) {
            $tableName = reset($table);
            $this->line('Optimizing: ' . $tableName);
            \DB::statement('OPTIMIZE TABLE ' . $tableName);
        }

        $this->info('Database optimized successfully!');
    } catch (\Exception $e) {
        $this->error('Error: ' . $e->getMessage());
        return 1;
    }

    return 0;
})->purpose('Optimize database tables');

/**
 * Command: php artisan log:clear
 * 
 * Clear all log files
 * Usage: php artisan log:clear
 */
Artisan::command('log:clear', function () {
    $this->info('Clearing log files...');

    try {
        $logPath = storage_path('logs');
        $files = glob($logPath . '/*.log');

        foreach ($files as $file) {
            $this->line('Deleting: ' . basename($file));
            unlink($file);
        }

        $this->info('Log files cleared successfully!');
    } catch (\Exception $e) {
        $this->error('Error: ' . $e->getMessage());
        return 1;
    }

    return 0;
})->purpose('Clear all log files');

/**
 * Command: php artisan commissions:status
 * 
 * Show commission processing status
 * Usage: php artisan commissions:status [--period=2024-01]
 */
Artisan::command('commissions:status', function () {
    $period = $this->option('period') ?? date('Y-m');

    $this->info('Commission status for period: ' . $period);

    try {
        $periodObj = \App\Models\CommissionPeriod::where('period', $period)->first();

        if (!$periodObj) {
            $this->warn('Period not found: ' . $period);
            return 1;
        }

        $stats = [
            'Period' => $periodObj->period,
            'Status' => $periodObj->status_label,
            'Start Date' => $periodObj->start_date,
            'End Date' => $periodObj->end_date,
            'Calculation Date' => $periodObj->calculation_date,
            'Payment Date' => $periodObj->payment_date,
            'Total Commissions' => '$' . number_format($periodObj->total_commissions, 2),
            'Total Paid' => '$' . number_format($periodObj->total_paid, 2),
            'Progress' => number_format($periodObj->progress, 1) . '%',
        ];

        $this->table(array_keys($stats), [$stats]);

        // Commission by type
        $byType = \App\Models\Commission::where('commission_period_id', $periodObj->id)
            ->select('type', \DB::raw('SUM(amount) as total'), \DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get();

        if ($byType->count() > 0) {
            $this->newLine();
            $this->info('Commissions by type:');
            $this->table(
                ['Type', 'Total', 'Count'],
                $byType->map(function ($item) {
                    return [
                        ucfirst($item->type),
                        '$' . number_format($item->total, 2),
                        $item->count,
                    ];
                })
            );
        }

    } catch (\Exception $e) {
        $this->error('Error: ' . $e->getMessage());
        return 1;
    }

    return 0;
})->purpose('Show commission processing status')
    ->addOption('period', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'Period to check (format: YYYY-MM)');

/**
 * Command: php artisan user:find-sponsor
 * 
 * Find a user by sponsor ID or email
 * Usage: php artisan user:find-sponsor --value=SALDEBF71
 */
Artisan::command('user:find-sponsor', function () {
    $value = $this->option('value');

    if (!$value) {
        $this->error('Please provide a sponsor ID or email using --value');
        return 1;
    }

    $this->info('Searching for: ' . $value);

    try {
        $user = \App\Models\User::where('sponsor_id', $value)
            ->orWhere('email', $value)
            ->orWhere('id', $value)
            ->first();

        if (!$user) {
            $this->warn('No user found: ' . $value);
            return 1;
        }

        $this->info('User found!');
        $this->table(
            ['ID', 'Name', 'Email', 'Sponsor ID', 'Rank', 'PV'],
            [[
                $user->id,
                $user->name,
                $user->email,
                $user->sponsor_id,
                $user->rank_name,
                $user->pv_balance,
            ]]
        );

    } catch (\Exception $e) {
        $this->error('Error: ' . $e->getMessage());
        return 1;
    }

    return 0;
})->purpose('Find a user by sponsor ID or email')
    ->addOption('value', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Sponsor ID or email to search');

/**
 * Command: php artisan rank:promotions
 * 
 * Show rank promotions history
 * Usage: php artisan rank:promotions [--user=1] [--limit=10]
 */
Artisan::command('rank:promotions', function () {
    $userId = $this->option('user');
    $limit = $this->option('limit') ?? 10;

    $this->info('Rank promotions history' . ($userId ? ' for user ID: ' . $userId : ''));

    try {
        $query = \App\Models\RankHistory::with(['user', 'oldRank', 'newRank'])
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $history = $query->get();

        if ($history->isEmpty()) {
            $this->warn('No rank history found.');
            return 1;
        }

        $this->table(
            ['Date', 'User', 'Old Rank', 'New Rank', 'Type'],
            $history->map(function ($item) {
                $oldLevel = $item->oldRank ? $item->oldRank->level : 0;
                $newLevel = $item->newRank ? $item->newRank->level : 0;
                $type = $newLevel > $oldLevel ? 'Promotion' : ($newLevel < $oldLevel ? 'Demotion' : 'Update');

                return [
                    $item->created_at->format('Y-m-d H:i'),
                    $item->user->name ?? 'N/A',
                    $item->old_rank_name ?? 'N/A',
                    $item->new_rank_name ?? 'N/A',
                    $type,
                ];
            })
        );

    } catch (\Exception $e) {
        $this->error('Error: ' . $e->getMessage());
        return 1;
    }

    return 0;
})->purpose('Show rank promotions history')
    ->addOption('user', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'Filter by user ID')
    ->addOption('limit', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'Limit results', 10);

// ============================================================
// FORMATAGE UTILITAIRE
// ============================================================