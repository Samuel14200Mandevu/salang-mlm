<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = [
            [
                'title' => '🚀 Lancement du nouveau package',
                'description' => 'Découvrez notre nouveau package Emerald avec des avantages exclusifs.',
                'date' => '15 Juillet 2026',
                'color' => 'primary'
            ],
            [
                'title' => '🏆 Compétition de parrainage',
                'description' => 'Gagnez des bonus exceptionnels en parrainant le plus de membres.',
                'date' => '1-31 Août 2026',
                'color' => 'yellow'
            ],
            [
                'title' => '🎓 Formation MLM en ligne',
                'description' => 'Apprenez les meilleures stratégies pour développer votre réseau.',
                'date' => '5 Septembre 2026',
                'color' => 'blue'
            ],
            [
                'title' => '💎 Promotion Spéciale',
                'description' => 'Réductions exceptionnelles sur tous les packages pendant 1 semaine.',
                'date' => '10-17 Octobre 2026',
                'color' => 'purple'
            ],
        ];
        
        return view('events.index', compact('events'));
    }
}
