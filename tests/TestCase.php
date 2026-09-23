<?php

namespace JeffersonGoncalves\Filament\Oidc\Tests;

use Filament\FilamentServiceProvider;
use Filament\Support\SupportServiceProvider;
use JeffersonGoncalves\Filament\Oidc\FilamentOidcServiceProvider;
use JeffersonGoncalves\Filament\Oidc\Tests\Fixtures\TestPanelProvider;
use JeffersonGoncalves\Filament\Oidc\Tests\Fixtures\User;
use JeffersonGoncalves\LaravelOidc\OidcServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\SocialiteServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            SupportServiceProvider::class,
            FilamentServiceProvider::class,
            SocialiteServiceProvider::class,
            OidcServiceProvider::class,
            FilamentOidcServiceProvider::class,
            TestPanelProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Socialite' => Socialite::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', $this->testing_connection());

        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        config()->set('auth.providers.users.model', User::class);

        config()->set('filament-oidc.user_model', User::class);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    /**
     * The original in-memory SQLite connection by default; CI (tests.yml) sets
     * OIDC_TEST_DB_* to run the same suite on MySQL and PostgreSQL. Not DB_CONNECTION:
     * Testbench pins it to "testing", which would always win over a driver read from it.
     *
     * @return array<string, mixed>
     */
    protected function testing_connection(): array
    {
        $driver = env('OIDC_TEST_DB_DRIVER', 'sqlite');

        if ($driver === 'sqlite') {
            return [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ];
        }

        return [
            'driver' => $driver,
            'host' => env('OIDC_TEST_DB_HOST', '127.0.0.1'),
            'port' => env('OIDC_TEST_DB_PORT'),
            'database' => env('OIDC_TEST_DB_DATABASE', 'testing'),
            'username' => env('OIDC_TEST_DB_USERNAME', 'root'),
            'password' => env('OIDC_TEST_DB_PASSWORD', ''),
            'charset' => $driver === 'pgsql' ? 'utf8' : 'utf8mb4',
            'prefix' => '',
        ];
    }
}
