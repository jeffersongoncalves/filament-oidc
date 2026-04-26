<?php

namespace JeffersonGoncalves\Filament\Oidc\Support;

use Filament\Panel;
use JeffersonGoncalves\Filament\Oidc\Exceptions\OidcAuthenticationException;
use JeffersonGoncalves\Filament\Oidc\FilamentOidcPlugin;
use JeffersonGoncalves\LaravelOidc\Data\OidcConfig;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;

class SocialiteConfigurator
{
    public static function for(Panel $panel, FilamentOidcPlugin $plugin): Provider
    {
        $issuerUrl = $plugin->getIssuerUrl();
        $clientId = $plugin->getClientId();
        $clientSecret = $plugin->getClientSecret();

        if ($issuerUrl === null || $clientId === null || $clientSecret === null) {
            throw OidcAuthenticationException::callbackFailed();
        }

        $config = new OidcConfig(
            issuerUrl: $issuerUrl,
            clientId: $clientId,
            clientSecret: $clientSecret,
            redirectUri: static::callbackUrl($panel),
            scopes: $plugin->getScopes(),
        );

        $driver = Socialite::driver('oidc');

        if (method_exists($driver, 'setConfig')) {
            $driver->setConfig($config);
        }

        return $driver;
    }

    public static function callbackUrl(Panel $panel): string
    {
        return route("filament.{$panel->getId()}.oidc.callback");
    }

    public static function redirectUrl(Panel $panel): string
    {
        return route("filament.{$panel->getId()}.oidc.redirect");
    }
}
