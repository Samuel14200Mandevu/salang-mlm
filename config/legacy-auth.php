<?php

return [

    /*
    | Comptes dont le mot de passe est encore au format MD5 (import ancien système).
    | La connexion en ligne est suspendue jusqu'à mise à jour en agence.
    */

    'login_error' => 'Votre compte utilise encore l\'ancien système de connexion. Consultez la page d\'information pour la marche à suivre.',

    'info_title' => 'Mise à jour de vos identifiants',

    'info_lead' => 'Votre compte a été créé avec l\'ancien système Salang. Pour des raisons de sécurité, la connexion en ligne avec l\'ancien mot de passe n\'est plus possible.',

    'info_steps' => [
        'Rendez-vous dans votre centre Salang (caisse / accueil distributeurs) avec une pièce d\'identité.',
        'Indiquez l\'adresse email de votre compte membre.',
        'Un responsable vérifiera votre identité et vous remettra un nouveau mot de passe sécurisé.',
        'Reconnectez-vous sur cette page avec votre email et le mot de passe remis sur place.',
    ],

    'info_contact_hint' => 'Si vous ne pouvez pas vous déplacer, contactez le support Salang en précisant votre nom complet et votre email de compte.',

];
