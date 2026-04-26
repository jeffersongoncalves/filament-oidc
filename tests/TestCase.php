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
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        config()->set('auth.providers.users.model', User::class);

        config()->set('filament-oidc.user_model', User::class);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
