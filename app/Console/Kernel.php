protected function schedule(Schedule $schedule): void
{
    // Toutes les 5 minutes - Traiter les commissions en attente
    $schedule->command('commissions:process')->everyFiveMinutes();
    
    // Toutes les heures - Traiter les retraits
    $schedule->command('withdrawals:process')->hourly();
    
    // Chaque jour à minuit - Mettre à jour les grades
    $schedule->command('ranks:update')->dailyAt('00:00');
    
    // Chaque jour à 01:00 - Calculer les commissions
    $schedule->command('commissions:calculate --all')->dailyAt('01:00');
    
    // Chaque jour à 02:00 - Générer le rapport
    $schedule->command('report:generate')->dailyAt('02:00');
    
    // Chaque semaine - Nettoyer les logs
    $schedule->command('logs:clean')->weekly();
}