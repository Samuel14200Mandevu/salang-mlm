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
        $commissionSettings = config('commission', [
            'rates' => [
                'direct' => 30,
                'indirect' => 15,
                'leadership' => 10,
                'retail' => 25,
            ],
            'leadership' => [
                'min_pv' => 1000,
                'max_levels' => 5,
            ],
            'withdrawal_fee' => 2.5,
            'min_withdrawal' => 10,
            'levels' => [
                1 => 30,
                2 => 15,
                3 => 10,
                4 => 5,
                5 => 5,
            ],
        ]);

        return view('admin.settings.commission', compact('commissionSettings'));
    }

    public function updateCommission(Request $request)
    {
        $request->validate([
            'direct_rate' => 'required|numeric|min:0|max:100',
            'indirect_rate' => 'required|numeric|min:0|max:100',
            'leadership_rate' => 'required|numeric|min:0|max:100',
            'retail_rate' => 'required|numeric|min:0|max:100',
            'leadership_min_pv' => 'required|integer|min:0',
            'leadership_max_levels' => 'required|integer|min:1|max:10',
            'withdrawal_fee' => 'required|numeric|min:0|max:100',
            'min_withdrawal' => 'required|numeric|min:0',
            'level1' => 'required|numeric|min:0|max:100',
            'level2' => 'required|numeric|min:0|max:100',
            'level3' => 'required|numeric|min:0|max:100',
            'level4' => 'required|numeric|min:0|max:100',
            'level5' => 'required|numeric|min:0|max:100',
        ]);

        $config = [
            'rates' => [
                'direct' => (float) $request->direct_rate,
                'indirect' => (float) $request->indirect_rate,
                'leadership' => (float) $request->leadership_rate,
                'retail' => (float) $request->retail_rate,
            ],
            'leadership' => [
                'min_pv' => (int) $request->leadership_min_pv,
                'max_levels' => (int) $request->leadership_max_levels,
            ],
            'withdrawal_fee' => (float) $request->withdrawal_fee,
            'min_withdrawal' => (float) $request->min_withdrawal,
            'levels' => [
                1 => (float) $request->level1,
                2 => (float) $request->level2,
                3 => (float) $request->level3,
                4 => (float) $request->level4,
                5 => (float) $request->level5,
            ],
        ];

        $this->updateConfigFile('commission.php', $config);

        Cache::forget('commission_config');
        Artisan::call('config:clear');

        Log::info('Commission settings updated', [
            'admin_id' => auth()->id(),
            'config' => $config,
        ]);

        return redirect()->route('admin.settings.commission')
            ->with('success', 'Commission settings updated.');
    }

    public function payment()
    {
        $paymentSettings = config('payment', [
            'gateways' => [
                'crypto' => [
                    'enabled' => true,
                    'networks' => ['TRC20', 'ERC20', 'BEP20'],
                ],
                'mobile_money' => [
                    'enabled' => true,
                    'providers' => ['Airtel Money', 'Orange Money', 'M-Pesa'],
                ],
            ],
            'fees' => [
                'crypto' => 0.5,
                'mobile_money' => 1.5,
                'bank_transfer' => 0.5,
            ],
        ]);

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