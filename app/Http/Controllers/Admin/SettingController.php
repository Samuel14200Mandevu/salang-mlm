<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    /**
     * Page des paramètres généraux
     */
    public function index()
    {
        $settings = [
            'site_name' => config('app.name'),
            'site_url' => config('app.url'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'maintenance_mode' => app()->isDownForMaintenance(),
            'debug_mode' => config('app.debug'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Mettre à jour les paramètres
     */
    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'timezone' => 'required|string|timezone',
            'locale' => 'required|string|in:fr,en',
            'debug_mode' => 'boolean',
            'maintenance_mode' => 'boolean',
        ]);

        try {
            // Mettre à jour le .env
            $this->updateEnv([
                'APP_NAME' => $request->site_name,
                'APP_TIMEZONE' => $request->timezone,
                'APP_LOCALE' => $request->locale,
                'APP_DEBUG' => $request->has('debug_mode') ? 'true' : 'false',
            ]);

            // Mode maintenance
            if ($request->has('maintenance_mode')) {
                Artisan::call('down', ['--retry' => 60]);
            } else {
                Artisan::call('up');
            }

            // Vider le cache
            Cache::flush();

            return redirect()->route('admin.settings')
                ->with('success', '⚙️ Paramètres mis à jour avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour paramètres', [
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Paramètres des commissions
     */
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
        ]);

        return view('admin.settings.commission', compact('commissionSettings'));
    }

    /**
     * Mettre à jour les paramètres des commissions
     */
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
        ]);

        // Mettre à jour config/commission.php
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
        ];

        $this->updateConfigFile('commission.php', $config);

        Cache::forget('commission_config');

        return redirect()->route('admin.settings.commission')
            ->with('success', '💰 Paramètres des commissions mis à jour.');
    }

    /**
     * Paramètres des paiements
     */
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

    /**
     * Mettre à jour les paramètres des paiements
     */
    public function updatePayment(Request $request)
    {
        $request->validate([
            'crypto_enabled' => 'boolean',
            'mobile_money_enabled' => 'boolean',
            'crypto_networks' => 'nullable|array',
            'mobile_money_providers' => 'nullable|array',
            'crypto_fee' => 'required|numeric|min:0|max:100',
            'mobile_money_fee' => 'required|numeric|min:0|max:100',
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
                'bank_transfer' => (float) $request->bank_transfer_fee ?? 0.5,
            ],
        ];

        $this->updateConfigFile('payment.php', $config);

        Cache::forget('payment_config');

        return redirect()->route('admin.settings.payment')
            ->with('success', '💳 Paramètres des paiements mis à jour.');
    }

    /**
     * Mettre à jour le fichier .env
     */
    private function updateEnv($data)
    {
        $path = base_path('.env');

        if (!file_exists($path)) {
            return;
        }

        $content = file_get_contents($path);

        foreach ($data as $key => $value) {
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

    /**
     * Mettre à jour un fichier de configuration
     */
    private function updateConfigFile($filename, $data)
    {
        $path = config_path($filename);

        $content = "<?php\n\nreturn " . var_export($data, true) . ";\n";

        file_put_contents($path, $content);
    }

    /**
     * Vider le cache
     */
    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        return redirect()->route('admin.settings')
            ->with('success', '🧹 Cache vidé avec succès.');
    }

    /**
     * Optimiser l'application
     */
    public function optimize()
    {
        Artisan::call('optimize');

        return redirect()->route('admin.settings')
            ->with('success', '🚀 Application optimisée.');
    }
}