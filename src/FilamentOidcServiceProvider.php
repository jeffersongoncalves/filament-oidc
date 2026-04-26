<?php

namespace JeffersonGoncalves\Filament\Oidc;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentOidcServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-oidc')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews()
            ->hasMigration('create_oidc_identities_table');
    }
}
