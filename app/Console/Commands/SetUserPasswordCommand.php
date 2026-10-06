<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\UserPassword;
use Illuminate\Console\Command;

class SetUserPasswordCommand extends Command
{
    protected $signature = 'user:set-password {email : Adresse email} {password : Nouveau mot de passe (min. 8 caractères)}';

    protected $description = 'Définir un mot de passe Bcrypt pour un utilisateur (comptes legacy MD5 inclus)';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $password = (string) $this->argument('password');

        if (strlen($password) < 8) {
            $this->error('Le mot de passe doit contenir au moins 8 caractères.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Aucun utilisateur pour {$email}.");

            return self::FAILURE;
        }

        $wasLegacy = !UserPassword::isBcrypt((string) $user->getRawOriginal('password'));

        $user->password = $password;
        $user->saveQuietly();

        $this->info("Mot de passe mis à jour pour {$email}".($wasLegacy ? ' (ancien hash MD5 remplacé)' : '').'.');

        return self::SUCCESS;
    }
}
