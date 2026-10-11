<?php

namespace Database\Seeders;

use App\Services\CareerQualificationPublicationsSync;
use Illuminate\Database\Seeder;

class CareerQualificationPublicationsSeeder extends Seeder
{
    public function run(): void
    {
        app(CareerQualificationPublicationsSync::class)->sync();
        $this->command?->info('✅ Publications bonus carrière (niv. 4–9) synchronisées.');
    }
}
