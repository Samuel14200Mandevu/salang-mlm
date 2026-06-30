protected function schedule(Schedule $schedule)
{
    // Exécuter les commissions toutes les 5 minutes
    $schedule->command('commissions:process')->everyFiveMinutes();
}
