<?php

namespace Tests;

use App\Models\User;
use Laravel\Dusk\Browser;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use RuntimeException;

abstract class DuskTestCase extends BaseTestCase
{
    protected function duskUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'is_active' => true,
            'kyc_status' => 'verified',
            'user_type' => 'member',
            'password' => 'password',
        ], $overrides));
    }

    protected function loginViaBrowser(Browser $browser, User $user, string $password = 'password'): Browser
    {
        return $browser->visit('/login')
            ->waitFor('#loginForm', 10)
            ->assertPathIs('/login')
            ->type('#email', $user->email)
            ->type('#password', $password)
            ->keys('#password', '{enter}')
            ->waitForLocation('/services', 15);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        $forbidden = ['salang_mlm', 'production', 'prod'];
        foreach ($forbidden as $name) {
            if (str_contains((string) $database, $name)) {
                throw new RuntimeException(
                    "DUSK refuse de tourner sur la base « {$database} ».\n".
                    "Utiliser SQLite avec --env=dusk (voir docs/DUSK.md)."
                );
            }
        }

        if ($connection !== 'sqlite') {
            throw new RuntimeException(
                "DUSK exige SQLite. Connexion actuelle : {$connection}\n".
                'Utiliser --env=dusk.'
            );
        }

        $databasePath = env('DB_DATABASE', database_path('dusk.sqlite'));
        if (! str_starts_with($databasePath, '/') && ! str_starts_with($databasePath, ':')) {
            $databasePath = base_path($databasePath);
        }

        if ($databasePath === ':memory:') {
            throw new RuntimeException(
                "DUSK exige le fichier database/dusk.sqlite (pas :memory:).\n".
                'Lancer avec --env=dusk.'
            );
        }

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $databasePath,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');
    }

    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
            '--disable-dev-shm-usage',
            '--no-sandbox',
            '--user-data-dir='.storage_path('dusk-chrome-profile'),
            '--remote-debugging-port=9222',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        $chromeBinary = env('DUSK_CHROME_BINARY');
        if (! $chromeBinary) {
            foreach (['/snap/bin/chromium', '/usr/bin/chromium-browser', '/usr/bin/google-chrome'] as $candidate) {
                if (is_executable($candidate)) {
                    $chromeBinary = $candidate;
                    break;
                }
            }
        }
        if ($chromeBinary) {
            $options->setBinary($chromeBinary);
        }

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
