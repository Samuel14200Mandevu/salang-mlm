<?php
// routes/console.php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// ============================================================
// COMMANDES ARTISAN PERSONNALISÉES
// ============================================================

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// commissions:process et ranks:update → classes dans app/Console/Commands (évite doublons)

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

Artisan::command('commissions:remind', function () {
    $this->info('Sending commission reminders...');
    return 0;
})->purpose('Send reminders for pending commissions');

Artisan::command('db:backup', function () {
    $path = $this->option('path') ?? storage_path('backups');
    $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    try {
        if (!is_dir($path)) mkdir($path, 0755, true);
        $fullPath = $path . '/' . $filename;
        $cnfPath = $path . '/.my.cnf.' . getmypid();
        file_put_contents($cnfPath, sprintf(
            "[client]\nuser=%s\npassword=%s\nhost=%s\n",
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            env('DB_HOST')
        ));
        chmod($cnfPath, 0600);

        $command = sprintf(
            'mysqldump --defaults-extra-file=%s %s > %s',
            escapeshellarg($cnfPath),
            escapeshellarg(env('DB_DATABASE')),
            escapeshellarg($fullPath)
        );
        exec($command, $output, $returnCode);
        @unlink($cnfPath);
        if ($returnCode !== 0) throw new \Exception('Backup failed: ' . $returnCode);
        $this->info('Backup created: ' . $fullPath);
    } catch (\Exception $e) {
        $this->error('Error: ' . $e->getMessage());
        return 1;
    }
    return 0;
})->purpose('Backup database')
    ->addOption('path', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'Backup path', storage_path('backups'));

Artisan::command('db:optimize', function () {
    try {
        $tables = \DB::select('SHOW TABLES');
        foreach ($tables as $table) {
            \DB::statement('OPTIMIZE TABLE ' . reset($table));
        }
        $this->info('Database optimized!');
    } catch (\Exception $e) {
        $this->error('Error: ' . $e->getMessage());
        return 1;
    }
    return 0;
})->purpose('Optimize database tables');

Artisan::command('log:clear', function () {
    try {
        $files = glob(storage_path('logs') . '/*.log');
        foreach ($files as $file) unlink($file);
        $this->info('Log files cleared!');
    } catch (\Exception $e) {
        $this->error('Error: ' . $e->getMessage());
        return 1;
    }
    return 0;
})->purpose('Clear log files');

Artisan::command('commissions:status', function () {
    $period = $this->option('period') ?? \App\Support\MlmPeriod::current();
    $periodObj = \App\Models\CommissionPeriod::where('period', $period)->first();
    if (!$periodObj) {
        $this->warn('Period not found: ' . $period);
        return 1;
    }
    $stats = [
        'Period'      => $periodObj->period,
        'Status'      => $periodObj->status_label,
        'Start'       => $periodObj->start_date,
        'End'         => $periodObj->end_date,
        'Payment'     => $periodObj->payment_date,
        'Total Comm.' => '$' . number_format($periodObj->total_commissions, 2),
        'Total Paid'  => '$' . number_format($periodObj->total_paid, 2),
        'Progress'    => number_format($periodObj->progress, 1) . '%',
    ];
    $this->table(array_keys($stats), [$stats]);
    return 0;
})->purpose('Show commission status')
    ->addOption('period', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'Period (YYYY-MM)');

Artisan::command('user:find-sponsor', function () {
    $value = $this->option('value');
    if (!$value) {
        $this->error('Provide --value');
        return 1;
    }
    $user = \App\Models\User::where('sponsor_id', $value)
        ->orWhere('email', $value)
        ->orWhere('id', $value)
        ->first();
    if (!$user) {
        $this->warn('Not found: ' . $value);
        return 1;
    }
    $this->table(
        ['ID', 'Name', 'Email', 'Sponsor', 'Rank', 'PV'],
        [[$user->id, $user->name, $user->email, $user->sponsor_id, $user->rank_name, $user->pv_balance]]
    );
    return 0;
})->purpose('Find user')
    ->addOption('value', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Value');

Artisan::command('rank:promotions', function () {
    $userId = $this->option('user');
    $limit = $this->option('limit') ?? 10;
    $query = \App\Models\RankHistory::with(['user', 'oldRank', 'newRank'])
        ->orderBy('created_at', 'desc')
        ->limit($limit);
    if ($userId) $query->where('user_id', $userId);
    $history = $query->get();
    if ($history->isEmpty()) {
        $this->warn('No rank history.');
        return 1;
    }
    $this->table(
        ['Date', 'User', 'Old', 'New'],
        $history->map(fn($i) => [
            $i->created_at->format('Y-m-d H:i'),
            $i->user->name ?? 'N/A',
            $i->old_rank_name ?? 'N/A',
            $i->new_rank_name ?? 'N/A',
        ])
    );
    return 0;
})->purpose('Show rank promotions')
    ->addOption('user', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'User ID')
    ->addOption('limit', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'Limit', 10);

// ════════════════════════════════════════════════════════════════════
// ✅ SCHEDULER — CALENDRIER MLM (8 → 7)
// ════════════════════════════════════════════════════════════════════
//
// RÈGLES :
//   - Reset PV mensuels       : le 8 à 00h00 (début nouveau mois MLM)
//   - Pipeline complet        : le 8 à 00h05 (PV + rangs + commissions)
//   - Paiements              : le 15 à 00h00 (mois M+1)
//   - Santé MLM              : toutes les heures (vérif incohérences)
//
// ════════════════════════════════════════════════════════════════════

// ─── 1. RESET PV MENSUELS (le 8 à 00h00) ────────────────────────────
Schedule::command('mlm:reset-monthly-pv')
    ->monthlyOn(8, '00:00')
    ->name('mlm-reset-monthly-pv')
    ->withoutOverlapping(30)
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/mlm-reset-monthly-pv.log'));

// ─── 2. PIPELINE COMPLET (le 8 à 00h05) ─────────────────────────────
Schedule::command('commissions:calculate-period --full')
    ->monthlyOn(8, '00:05')
    ->name('mlm-calculate-period')
    ->withoutOverlapping(120)
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/mlm-calculate-period.log'));

// ─── 3. PAIEMENTS (le 15 à 00h00) ───────────────────────────────────
Schedule::command('commissions:generate-payments')
    ->monthlyOn(15, '00:00')
    ->name('mlm-generate-payments')
    ->withoutOverlapping(120)
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/mlm-generate-payments.log'));

// ─── 4. SANTÉ MLM (toutes les heures) ───────────────────────────────
Schedule::command('mlm:fix-team-pv --dry-run')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/mlm-health.log'));

// ─── 5. GARDE-FOU : Période courante existe (tous les jours à 00h30) ─
Schedule::call(function () {
    \App\Models\CommissionPeriod::getCurrentPeriod();
})
    ->dailyAt('00:30')
    ->name('mlm-ensure-current-period')
    ->onOneServer();
