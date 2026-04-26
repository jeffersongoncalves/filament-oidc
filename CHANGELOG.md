# Changelog

All notable changes to `filament-oidc` will be documented in this file.

## v1.0.0 - 2026-04-25

First public release of `filament-oidc`.

### Features

- Drop-in OpenID Connect SSO for Filament v5 panels, powered by [`jeffersongoncalves/laravel-oidc`](https://github.com/jeffersongoncalves/laravel-oidc)
- Multi-panel support: routes, callback URLs and guards are scoped per panel (`filament.{panel-id}.oidc.{redirect|callback|logout}`)
- Polymorphic `oidc_identities` table — never alters the host application's `users` table
- Auto user provisioning enabled by default with email matching fallback and configurable resolver/attributes
- Fluent plugin API: `guard`, `userModel`, `issuerUrl`, `clientId`, `clientSecret`, `scopes`, `autoCreateUsers`, `logoutFromIdp`, `position`, `buttonLabel`, `buttonIcon`, `userAttributesUsing`, `resolveUserUsing`, `redirectAfterLogin`
- RP-initiated logout via the IdP's `end_session_endpoint`
- Events: `OidcUserAuthenticated`, `OidcUserCreated`
- Translations: `en`, `pt_BR`, `es`
- Pest test suite (12 tests, 44 assertions) covering registration, redirect, callback, provisioning and logout flows

### Compatibility

| Plugin branch | Filament |
|---------------|----------|
| `1.x`         | v5       |

### Installation

```bash
composer require jeffersongoncalves/filament-oidc
php artisan vendor:publish --tag="filament-oidc-migrations"
php artisan migrate

```
See the [README](https://github.com/jeffersongoncalves/filament-oidc/blob/1.x/README.md) for full configuration and multi-panel examples.

## Unreleased
