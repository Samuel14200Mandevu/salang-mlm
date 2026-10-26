<?php

/**
 * Accueil membre — contenu et mise en page selon le grade (niveau MLM 0–9).
 *
 * stats_set : entry (débutant) | growth (progression) | leader (leadership)
 */

return [
    0 => [
        'gradient' => '#64748b', // insigne / neutre
        'badge_color' => 'slate',
        'welcome_title' => 'Bienvenue chez Salang',
        'welcome_message' => 'Activez votre compte, découvrez la boutique et votre parcours membre.',
        'show_chart' => false,
        'show_leaders' => false,
        'show_max_banner' => false,
        'stats_set' => 'entry',
        'quick_actions' => ['products', 'packages', 'team'],
        'welcome_ctas' => ['products', 'packages'],
    ],
    1 => [
        'gradient' => '#8f5c3a', // distributeur.png — bronze
        'badge_color' => 'amber',
        'welcome_title' => 'Bienvenue dans votre aventure !',
        'welcome_message' => 'Découvrez votre tableau de bord et commencez à bâtir votre réseau.',
        'show_chart' => true,
        'show_leaders' => false,
        'show_max_banner' => false,
        'stats_set' => 'entry',
        'quick_actions' => ['packages', 'products', 'withdrawal', 'team'],
        'welcome_ctas' => ['products', 'team'],
    ],
    2 => [
        'gradient' => '#355376', // qualification.png — acier
        'badge_color' => 'blue',
        'welcome_title' => 'Continuez votre progression !',
        'welcome_message' => 'Votre réseau grandit, maintenez le rythme.',
        'show_chart' => true,
        'show_leaders' => false,
        'show_max_banner' => false,
        'stats_set' => 'growth',
        'quick_actions' => ['packages', 'products', 'withdrawal', 'team'],
        'welcome_ctas' => ['products', 'team'],
    ],
    3 => [
        'gradient' => '#9a6b1f', // cumul directeur.png — or
        'badge_color' => 'amber',
        'welcome_title' => 'Qualification en vue !',
        'welcome_message' => 'Votre réseau se développe — suivez vos filleuls performants.',
        'show_chart' => true,
        'show_leaders' => true,
        'show_max_banner' => false,
        'stats_set' => 'growth',
        'quick_actions' => ['packages', 'products', 'withdrawal', 'team'],
        'welcome_ctas' => ['team', 'grades'],
    ],
    4 => [
        'gradient' => '#86262d', // directeur.png — rubis
        'badge_color' => 'rose',
        'welcome_title' => 'Directeur — pilotage d’équipe',
        'welcome_message' => 'Vous êtes un leader confirmé : suivez commissions et structure.',
        'show_chart' => true,
        'show_leaders' => true,
        'show_max_banner' => false,
        'stats_set' => 'growth',
        'quick_actions' => ['network', 'commissions', 'wallet', 'grades'],
        'welcome_ctas' => ['network', 'commissions'],
    ],
    5 => [
        'gradient' => '#305428', // manager senior.png — vert
        'badge_color' => 'green',
        'welcome_title' => 'Manager Senior',
        'welcome_message' => 'Votre leadership inspire votre équipe.',
        'show_chart' => true,
        'show_leaders' => true,
        'show_max_banner' => false,
        'stats_set' => 'leader',
        'quick_actions' => ['network', 'commissions', 'wallet', 'grades'],
        'welcome_ctas' => ['commissions', 'network'],
    ],
    6 => [
        'gradient' => '#3d5f5a', // directeur de l'envole.png — teal
        'badge_color' => 'teal',
        'welcome_title' => 'Directeur Envolée',
        'welcome_message' => 'Vous faites partie des meilleurs — scalez votre organisation.',
        'show_chart' => true,
        'show_leaders' => true,
        'show_max_banner' => false,
        'stats_set' => 'leader',
        'quick_actions' => ['network', 'commissions', 'wallet', 'grades'],
        'welcome_ctas' => ['network', 'wallet'],
    ],
    7 => [
        'gradient' => '#1e3485', // saphire manager.png — saphir
        'badge_color' => 'indigo',
        'welcome_title' => 'Saphire Manager',
        'welcome_message' => 'Vous êtes une référence dans votre réseau.',
        'show_chart' => true,
        'show_leaders' => true,
        'show_max_banner' => false,
        'stats_set' => 'leader',
        'quick_actions' => ['network', 'commissions', 'wallet', 'grades'],
        'welcome_ctas' => ['grades', 'commissions'],
    ],
    8 => [
        'gradient' => '#2b5f9b', // diamant bleu.png
        'badge_color' => 'blue',
        'welcome_title' => 'Diamant Bleu',
        'welcome_message' => 'Votre influence s’étend — accompagnez vos leaders.',
        'show_chart' => true,
        'show_leaders' => true,
        'show_max_banner' => false,
        'stats_set' => 'leader',
        'quick_actions' => ['network', 'commissions', 'wallet', 'grades'],
        'welcome_ctas' => ['network', 'grades'],
    ],
    9 => [
        'gradient' => '#8a6e52', // diamant perle.png — champagne
        'badge_color' => 'amber',
        'welcome_title' => 'Perle Diamant',
        'welcome_message' => 'Sommet du plan de carrière — félicitations !',
        'show_chart' => true,
        'show_leaders' => true,
        'show_max_banner' => true,
        'stats_set' => 'leader',
        'quick_actions' => ['network', 'commissions', 'wallet', 'grades'],
        'welcome_ctas' => ['commissions', 'network'],
    ],
];
