<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        $messages = [
            [
                'sender' => 'Administrateur',
                'sender_initial' => 'A',
                'message' => 'Votre demande de retrait a été approuvée.',
                'time' => 'Il y a 2 heures',
                'is_read' => true,
            ],
            [
                'sender' => 'Support',
                'sender_initial' => 'S',
                'message' => 'Nous avons répondu à votre ticket #TICK-001.',
                'time' => 'Il y a 1 jour',
                'is_read' => false,
            ],
            [
                'sender' => 'Jean Dupont',
                'sender_initial' => 'J',
                'message' => 'Merci pour votre parrainage !',
                'time' => 'Il y a 3 jours',
                'is_read' => true,
            ],
            [
                'sender' => 'Marie Martin',
                'sender_initial' => 'M',
                'message' => 'Je viens de m\'inscrire grâce à votre lien.',
                'time' => 'Il y a 5 jours',
                'is_read' => false,
            ],
            [
                'sender' => 'Administrateur',
                'sender_initial' => 'A',
                'message' => 'Bienvenue sur la plateforme Salang MLM !',
                'time' => 'Il y a 1 semaine',
                'is_read' => true,
            ],
        ];
        
        return view('message.index', compact('messages'));
    }
}
