<?php

namespace Database\Seeders;

use App\Models\MlmSetting;
use Illuminate\Database\Seeder;

class SiteContactSeeder extends Seeder
{
    public function run(): void
    {
        MlmSetting::setValue(
            'support_email',
            config('salang-site.email', 'support@salanggroup.com'),
            'contact',
            'Email support officiel Salang Group'
        );

        MlmSetting::setValue(
            'support_phone',
            config('salang-site.phone', '+243 975 220 079'),
            'contact',
            'Téléphone support officiel Salang Group'
        );

        $this->command?->info('Contact site : support@salanggroup.com / +243 975 220 079');
    }
}
