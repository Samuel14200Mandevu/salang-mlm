<?php

namespace Database\Seeders;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoPublicationSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::role('admin')->first()
            ?? User::role('it_manager')->first()
            ?? User::query()->first();

        if (! $author) {
            $this->command?->warn('DemoPublicationSeeder : aucun utilisateur trouvé, seed ignoré.');

            return;
        }

        Publication::query()->firstOrCreate(
            [
                'title' => 'Convention annuelle Salang Group',
                'type' => Publication::TYPE_EVENT,
            ],
            [
                'user_id' => $author->id,
                'description' => 'Exemple d’événement pour la galerie Supervision (sans médias attachés).',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(2)->toDateString(),
                'is_published' => true,
            ]
        );

        Publication::query()->firstOrCreate(
            [
                'title' => 'Promotion du mois — packs bien-être',
                'type' => Publication::TYPE_PROMOTION,
            ],
            [
                'user_id' => $author->id,
                'description' => 'Exemple de promotion avec dates de validité.',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
                'is_published' => true,
            ]
        );

        $this->command?->info('✅ Publications de démo créées (événement + promotion).');
    }
}
