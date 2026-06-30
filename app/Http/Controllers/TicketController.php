<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = [
            [
                'id' => '#TICK-001',
                'title' => 'Problème de paiement',
                'status' => 'En cours',
                'status_color' => 'yellow',
                'created_at' => '2 jours',
            ],
            [
                'id' => '#TICK-002',
                'title' => 'Question sur les commissions',
                'status' => 'Résolu',
                'status_color' => 'green',
                'created_at' => '5 jours',
            ],
            [
                'id' => '#TICK-003',
                'title' => 'Demande de retrait',
                'status' => 'Fermé',
                'status_color' => 'red',
                'created_at' => '1 semaine',
            ],
            [
                'id' => '#TICK-004',
                'title' => 'Problème technique',
                'status' => 'En cours',
                'status_color' => 'yellow',
                'created_at' => '1 jour',
            ],
        ];
        
        return view('ticket.index', compact('tickets'));
    }
}
