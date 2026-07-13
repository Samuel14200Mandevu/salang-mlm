<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckPackageExpiry extends Command
{
    protected $signature = 'packages:check-expiry';
    protected $description = 'Vérifier les expirations de packages';

    public function handle(): void
    {
        $this->info('Vérification des expirations de packages...');
        
        $expired = User::where('package_expiry', '<', now())
            ->where('package_id', '!=', null)
            ->get();

        foreach ($expired as $user) {
            $user->package_id = null;
            $user->package_expiry = null;
            $user->save();
            
            Log::info('Package expiré pour l\'utilisateur', [
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);
        }

        $this->info("✅ {$expired->count()} packages expirés traités");
    }
}
