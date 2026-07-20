<?php
// app/Http/Controllers/Admin/SettingController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'site_name' => config('app.name'),
            'site_url' => config('app.url'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'maintenance_mode' => app()->isDownForMaintenance(),
            'debug_mode' => config('app.debug'),
            'env' => app()->environment(),
            'version' => app()->version(),
            'php_version' => phpversion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'max_upload_size' => ini_get('upload_max_filesize'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'timezone' => 'required|string|timezone',
            'locale' => 'required|string|in:fr,en',
            'debug_mode' => 'boolean',
        ]);

        try {
            $this->updateEnv([
                'APP_NAME' => $request->site_name,
                'APP_TIMEZONE' => $request->timezone,
                'APP_LOCALE' => $request->locale,
                'APP_DEBUG' => $request->has('debug_mode') ? 'true' : 'false',
            ]);

            Artisan::call('config:clear');

            Log::info('General settings updated', [
                'admin_id' => auth()->id(),
                'data' => $request->all(),
            ]);

            return redirect()->route('admin.settings')
                ->with('success', 'General settings updated successfully.');

        } catch (\Exception $e) {
            Log::error('Error updating general settings', [
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function toggleMaintenance(Request $request)
    {
        try {
            if (app()->isDownForMaintenance()) {
                Artisan::call('up');
                $message = 'Application is back online.';
            } else {
                $secret = $request->secret ?? null;
                $command = 'down --retry=60';
                if ($secret) {
                    $command .= " --secret={$secret}";
                }
                Artisan::call($command);
                $message = 'Application is in maintenance mode.';
            }

            Log::info('Maintenance mode changed', [
                'admin_id' => auth()->id(),
                'status' => app()->isDownForMaintenance() ? 'down' : 'up',
            ]);

            return redirect()->route('admin.settings')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error toggling maintenance mode', [
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function commission()
    {
        // Récupérer les données depuis le fichier de config
        $config = config('commission', []);
        
        // Construire le tableau de données pour la vue
        $commissionSettings = [
            'rates' => [
                'levels' => $config['rates']['levels'] ?? [
                    1 => 0, 2 => 0, 3 => 22, 4 => 26, 5 => 30, 
                    6 => 34, 7 => 40, 8 => 43, 9 => 45
                ],
                'leadership' => $config['rates']['leadership'] ?? [
                    5 => 0.5, 6 => 1.1, 7 => 1.8, 8 => 2.6, 9 => 3.5
                ],
                'retail' => $config['rates']['retail'] ?? 25,
                'consumer_bonus' => $config['rates']['consumer_bonus'] ?? 6,
                'global_bonus_pool' => $config['rates']['global_bonus_pool'] ?? 6,
            ],
            'leadership' => [
                'min_pv' => $config['leadership_conditions'][5]['personal_pv'] ?? 30,
                'max_levels' => 9,
            ],
            'withdrawal_fee' => $config['withdrawal_fee'] ?? 2.5,
            'min_withdrawal' => $config['min_withdrawal'] ?? 10,
            'rank_thresholds' => $config['rank_thresholds'] ?? [
                1 => 0, 2 => 100, 3 => 200, 4 => 1000, 5 => 3800,
                6 => 16000, 7 => 73000, 8 => 280000, 9 => 400000
            ],
            'monthly_pv_required' => $config['monthly_pv_required'] ?? [
                1 => 0, 2 => 20, 3 => 20, 4 => 25, 5 => 30,
                6 => 50, 7 => 100, 8 => 200, 9 => 300
            ],
            'leadership_conditions' => $config['leadership_conditions'] ?? [
                5 => ['personal_pv' => 30, 'group_pv' => 500],
                6 => ['personal_pv' => 50, 'group_pv' => 1000],
                7 => ['personal_pv' => 100, 'group_pv' => 2000],
                8 => ['personal_pv' => 180, 'group_pv' => 3000],
                9 => ['personal_pv' => 300, 'group_pv' => 5000],
            ],
            'levels' => $config['rates']['levels'] ?? [
                1 => 0, 2 => 0, 3 => 22, 4 => 26, 5 => 30, 
                6 => 34, 7 => 40, 8 => 43, 9 => 45
            ],
        ];

        return view('admin.settings.commission', compact('commissionSettings'));
    }

    public function updateCommission(Request $request)
    {
        $request->validate([
            // Niveaux 1-9
            'level_1' => 'required|numeric|min:0|max:100',
            'level_2' => 'required|numeric|min:0|max:100',
            'level_3' => 'required|numeric|min:0|max:100',
            'level_4' => 'required|numeric|min:0|max:100',
            'level_5' => 'required|numeric|min:0|max:100',
            'level_6' => 'required|numeric|min:0|max:100',
            'level_7' => 'required|numeric|min:0|max:100',
            'level_8' => 'required|numeric|min:0|max:100',
            'level_9' => 'required|numeric|min:0|max:100',
            // Leadership
            'leadership_5' => 'required|numeric|min:0|max:100',
            'leadership_6' => 'required|numeric|min:0|max:100',
            'leadership_7' => 'required|numeric|min:0|max:100',
            'leadership_8' => 'required|numeric|min:0|max:100',
            'leadership_9' => 'required|numeric|min:0|max:100',
            // Autres taux
            'retail_rate' => 'required|numeric|min:0|max:100',
            'consumer_bonus' => 'required|numeric|min:0|max:100',
            'global_bonus' => 'required|numeric|min:0|max:100',
            // Leadership conditions
            'leadership_min_pv' => 'required|integer|min:0',
            'leadership_max_levels' => 'required|integer|min:1|max:10',
            // Withdrawal
            'withdrawal_fee' => 'required|numeric|min:0|max:100',
            'min_withdrawal' => 'required|numeric|min:0',
        ]);

        // Construire la nouvelle configuration
        $config = [
            'rates' => [
                'levels' => [
                    1 => (float) $request->level_1,
                    2 => (float) $request->level_2,
                    3 => (float) $request->level_3,
                    4 => (float) $request->level_4,
                    5 => (float) $request->level_5,
                    6 => (float) $request->level_6,
                    7 => (float) $request->level_7,
                    8 => (float) $request->level_8,
                    9 => (float) $request->level_9,
                ],
                'leadership' => [
                    5 => (float) $request->leadership_5,
                    6 => (float) $request->leadership_6,
                    7 => (float) $request->leadership_7,
                    8 => (float) $request->leadership_8,
                    9 => (float) $request->leadership_9,
                ],
                'retail' => (float) $request->retail_rate,
                'consumer_bonus' => (float) $request->consumer_bonus,
                'global_bonus_pool' => (float) $request->global_bonus,
            ],
            'leadership_conditions' => [
                5 => [
                    'personal_pv' => (int) $request->leadership_min_pv,
                    'group_pv' => 500,
                ],
                6 => [
                    'personal_pv' => 50,
                    'group_pv' => 1000,
                ],
                7 => [
                    'personal_pv' => 100,
                    'group_pv' => 2000,
                ],
                8 => [
                    'personal_pv' => 180,
                    'group_pv' => 3000,
                ],
                9 => [
                    'personal_pv' => 300,
                    'group_pv' => 5000,
                ],
            ],
            'withdrawal_fee' => (float) $request->withdrawal_fee,
            'min_withdrawal' => (float) $request->min_withdrawal,
        ];

        // Sauvegarder dans le cache
        Cache::put('commission_config', $config);

        // Mettre à jour le fichier de config
        $this->updateConfigFile('commission.php', $config);

        // Vider le cache de configuration
        Artisan::call('config:clear');

        Log::info('Commission settings updated', [
            'admin_id' => auth()->id(),
            'config' => $config,
        ]);

        return redirect()->route('admin.settings.commission')
            ->with('success', 'Commission settings updated successfully.');
    }

    public function payment()
    {
        $paymentSettings = [
            'gateways' => [
                'crypto' => [
                    'enabled' => Cache::get('crypto_enabled', true),
                    'networks' => Cache::get('crypto_networks', ['TRC20', 'ERC20', 'BEP20']),
                ],
                'mobile_money' => [
                    'enabled' => Cache::get('mobile_money_enabled', true),
                    'providers' => Cache::get('mobile_money_providers', ['Airtel Money', 'Orange Money', 'M-Pesa']),
                ],
            ],
            'fees' => [
                'crypto' => (float) Cache::get('crypto_fee', 0.5),
                'mobile_money' => (float) Cache::get('mobile_money_fee', 1.5),
                'bank_transfer' => (float) Cache::get('bank_transfer_fee', 0.5),
            ],
        ];

        return view('admin.settings.payment', compact('paymentSettings'));
    }

    public function updatePayment(Request $request)
    {
        $request->validate([
            'crypto_enabled' => 'boolean',
            'mobile_money_enabled' => 'boolean',
            'crypto_networks' => 'nullable|array',
            'mobile_money_providers' => 'nullable|array',
            'crypto_fee' => 'required|numeric|min:0|max:100',
            'mobile_money_fee' => 'required|numeric|min:0|max:100',
            'bank_transfer_fee' => 'required|numeric|min:0|max:100',
        ]);

        Cache::put('crypto_enabled', $request->has('crypto_enabled'));
        Cache::put('mobile_money_enabled', $request->has('mobile_money_enabled'));
        Cache::put('crypto_networks', $request->crypto_networks ?? ['TRC20', 'ERC20', 'BEP20']);
        Cache::put('mobile_money_providers', $request->mobile_money_providers ?? ['Airtel Money', 'Orange Money', 'M-Pesa']);
        
        Cache::put('crypto_fee', (float) $request->crypto_fee);
        Cache::put('mobile_money_fee', (float) $request->mobile_money_fee);
        Cache::put('bank_transfer_fee', (float) $request->bank_transfer_fee);

        $config = [
            'gateways' => [
                'crypto' => [
                    'enabled' => $request->has('crypto_enabled'),
                    'networks' => $request->crypto_networks ?? ['TRC20', 'ERC20', 'BEP20'],
                ],
                'mobile_money' => [
                    'enabled' => $request->has('mobile_money_enabled'),
                    'providers' => $request->mobile_money_providers ?? ['Airtel Money', 'Orange Money', 'M-Pesa'],
                ],
            ],
            'fees' => [
                'crypto' => (float) $request->crypto_fee,
                'mobile_money' => (float) $request->mobile_money_fee,
                'bank_transfer' => (float) $request->bank_transfer_fee,
            ],
        ];

        $this->updateConfigFile('payment.php', $config);

        Cache::forget('payment_config');
        Artisan::call('config:clear');

        Log::info('Payment settings updated', [
            'admin_id' => auth()->id(),
            'config' => $config,
        ]);

        return redirect()->route('admin.settings.payment')
            ->with('success', 'Payment settings updated.');
    }

    private function updateEnv($data)
    {
        $path = base_path('.env');

        if (!file_exists($path)) {
            return;
        }

        $content = file_get_contents($path);

        foreach ($data as $key => $value) {
            if (strpos($value, ' ') !== false || strpos($value, '#') !== false) {
                $value = '"' . $value . '"';
            }

            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                $content .= "\n{$replacement}";
            }
        }

        file_put_contents($path, $content);
    }

    private function updateConfigFile($filename, $data)
    {
        $path = config_path($filename);

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $content = "<?php\n\nreturn " . var_export($data, true) . ";\n";

        file_put_contents($path, $content);
    }

    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('event:clear');
        Artisan::call('optimize:clear');

        Log::info('Cache cleared', [
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.settings')
            ->with('success', 'Cache cleared successfully.');
    }

    public function optimize()
    {
        Artisan::call('optimize');

        Log::info('Application optimized', [
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.settings')
            ->with('success', 'Application optimized.');
    }

    public function systemInfo()
    {
        $info = [
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'N/A',
            'max_upload_size' => ini_get('upload_max_filesize'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'post_max_size' => ini_get('post_max_size'),
            'disk_free_space' => $this->formatBytes(disk_free_space('/')),
            'disk_total_space' => $this->formatBytes(disk_total_space('/')),
            'database_connection' => config('database.default'),
            'database_name' => config('database.connections.mysql.database'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
        ];

        return response()->json($info);
    }

    private function formatBytes($bytes)
    {
        if ($bytes === false) return 'N/A';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < 4) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}