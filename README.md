<div class="filament-hidden">

![Filament OIDC](https://raw.githubusercontent.com/jeffersongoncalves/filament-oidc/1.x/art/jeffersongoncalves-filament-oidc.png)

</div>

# Filament OIDC

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/filament-oidc.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/filament-oidc)
[![PHPStan](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/filament-oidc/phpstan.yml?branch=1.x&label=phpstan&style=flat-square)](https://github.com/jeffersongoncalves/filament-oidc/actions/workflows/phpstan.yml)
[![Tests](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/filament-oidc/tests.yml?branch=1.x&label=tests&style=flat-square)](https://github.com/jeffersongoncalves/filament-oidc/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/filament-oidc.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/filament-oidc)

Drop-in OpenID Connect single sign-on for Filament v5 panels, powered by [`jeffersongoncalves/laravel-oidc`](https://github.com/jeffersongoncalves/laravel-oidc). Works in single- and multi-panel apps, supports per-panel guards, and stores OIDC identities in a polymorphic table so the host application's `users` table is never altered.

## Compatibility

| Plugin branch | Filament |
|---------------|----------|
| `1.x`         | v5       |

> This plugin is published only for Filament v5. Branch `1.x` is the first major of the plugin and does not follow the legacy table where `1.x` historically targeted Filament v3.

## Installation

```bash
composer require jeffersongoncalves/filament-oidc
```

Publish and run the migration that creates the `oidc_identities` table:

```bash
php artisan vendor:publish --tag="filament-oidc-migrations"
php artisan migrate
```

Optionally publish the configuration and translations:

```bash
php artisan vendor:publish --tag="filament-oidc-config"
php artisan vendor:publish --tag="filament-oidc-translations"
```

Configure the underlying `laravel-oidc` driver via the standard environment variables:

```env
OIDC_ISSUER_URL=https://idp.example.com
OIDC_CLIENT_ID=your-client-id
OIDC_CLIENT_SECRET=your-client-secret
```

> The plugin computes the redirect URI from the panel route, so you do **not** need to set `OIDC_REDIRECT_URI`. Register `https://your-app.test/{panel}/oidc/callback` with your Identity Provider for every panel that exposes the plugin.

## Registering the plugin in a panel

```php
use JeffersonGoncalves\Filament\Oidc\FilamentOidcPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->id('admin')
        ->path('admin')
        ->login()
        ->plugin(
            FilamentOidcPlugin::make()
                ->guard('web')
                ->autoCreateUsers(true)
        );
}
```

## Multi-panel setup

Register the plugin once per panel. Each panel gets its own routes (`filament.{panel-id}.oidc.{redirect|callback|logout}`), its own callback URL, and may opt into a different guard or user model.

```php
// AdminPanelProvider
$panel->plugin(
    FilamentOidcPlugin::make()
        ->guard('web')
        ->userModel(\App\Models\User::class)
);

// CustomerPanelProvider
$panel->plugin(
    FilamentOidcPlugin::make()
        ->guard('customer')
        ->userModel(\App\Models\Customer::class)
        ->autoCreateUsers(false)
);
```

Register the per-panel callback URL with your IdP:

```
https://your-app.test/admin/oidc/callback
https://your-app.test/customer/oidc/callback
```

## Linking identities to users

The plugin stores every IdP/subject pair in the `oidc_identities` table and links it to the authenticated model through a polymorphic relationship. Add the `HasOidcIdentities` trait to expose the `oidcIdentities()` relation on your authenticatable model:

```php
use JeffersonGoncalves\Filament\Oidc\Concerns\HasOidcIdentities;

class User extends Authenticatable
{
    use HasOidcIdentities;
}
```

The default callback handler:

1. Looks up `oidc_identities.{issuer, subject}`.
2. Falls back to matching the local user by email.
3. Creates a new user when `auto_create_users` is enabled, otherwise throws `OidcAuthenticationException::autoCreateDisabled()`.

## Customising user provisioning

Override either the attribute mapping or the full resolution closure:

```php
FilamentOidcPlugin::make()
    ->userAttributesUsing(fn (\Laravel\Socialite\Two\User $oidcUser) => [
        'name' => $oidcUser->getName(),
        'email' => $oidcUser->getEmail(),
        'tenant_id' => $oidcUser->user['tenant'] ?? null,
        'password' => bcrypt(\Illuminate\Support\Str::random(40)),
    ])
    ->resolveUserUsing(function (\App\Models\User $template, \Laravel\Socialite\Two\User $oidcUser) {
        return \App\Models\User::query()->updateOrCreate(
            ['email' => $oidcUser->getEmail()],
            ['name' => $oidcUser->getName()],
        );
    });
```

## Logout (RP-initiated)

Enable IdP logout to redirect users to the issuer's `end_session_endpoint` after the local logout completes:

```php
FilamentOidcPlugin::make()->logoutFromIdp(true);
```

Use the named route from your blade templates (e.g. inside a custom user menu):

```blade
<form method="POST" action="{{ route('filament.admin.oidc.logout') }}">
    @csrf
    <button type="submit">Log out</button>
</form>
```

## Events

| Event                                                                  | When                                                |
|------------------------------------------------------------------------|-----------------------------------------------------|
| `JeffersonGoncalves\Filament\Oidc\Events\OidcUserAuthenticated`        | Every successful callback, before the redirect.     |
| `JeffersonGoncalves\Filament\Oidc\Events\OidcUserCreated`              | Only when a brand-new user is provisioned.          |

## Testing

```bash
composer test
```

## Credits

- [Jefferson Gonçalves](https://github.com/jeffersongoncalves)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
