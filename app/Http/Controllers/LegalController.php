<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LegalController extends Controller
{
    /**
     * Détermine automatiquement d'où vient l'utilisateur
     */
    private function getBackRoute()
    {
        $referer = request()->headers->get('referer');
        
        if ($referer) {
            // Si l'utilisateur vient de la page d'inscription
            if (str_contains($referer, route('register'))) {
                return [
                    'route' => route('register'),
                    'label' => 'Retour à l\'inscription'
                ];
            }
            
            // Si l'utilisateur vient de la page de connexion
            if (str_contains($referer, route('login'))) {
                return [
                    'route' => route('login'),
                    'label' => 'Retour à la connexion'
                ];
            }
            
            // Si l'utilisateur vient du dashboard
            if (str_contains($referer, route('dashboard'))) {
                return [
                    'route' => route('dashboard'),
                    'label' => 'Retour au tableau de bord'
                ];
            }
        }
        
        // Par défaut : retour à l'accueil
        return [
            'route' => route('home'),
            'label' => 'Retour à l\'accueil'
        ];
    }

    /**
     * Affiche les conditions générales
     */
    public function terms()
    {
        $pageTitle = "Conditions Générales d'Utilisation";
        $back = $this->getBackRoute();
        return view('legal.terms', compact('pageTitle', 'back'));
    }

    /**
     * Affiche la politique de confidentialité
     */
    public function privacy()
    {
        $pageTitle = "Politique de Confidentialité";
        $back = $this->getBackRoute();
        return view('legal.privacy', compact('pageTitle', 'back'));
    }

    /**
     * Affiche les mentions légales
     */
    public function legal()
    {
        $pageTitle = "Mentions Légales";
        $back = $this->getBackRoute();
        return view('legal.legal', compact('pageTitle', 'back'));
    }
}