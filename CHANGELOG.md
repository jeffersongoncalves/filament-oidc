# Changelog

All notable changes to `filament-oidc` will be documented in this file.

## 1.1.0 - 2026-09-23

### What's new

- **Translations:** 16 new locales (ar, az, de, fa, fr, hi, it, ja, nl, pl, pt, ru, tr, uk, uz, zh_CN). (#9)

Thanks to @Elvin-Qulizade (Elvin Qulizada) for the i18n initiative behind these translations — first contributed in jeffersongoncalves/filament-scanner-guard#2 and now rolled out across the Filament plugins. He is credited as co-author.

### What's Changed

* build(deps): Bump actions/checkout from 6 to 7 by @dependabot[bot] in https://github.com/jeffersongoncalves/filament-oidc/pull/2
* docs: add Buy Me a Coffee sponsor link by @jeffersongoncalves in https://github.com/jeffersongoncalves/filament-oidc/pull/3
* docs: standardize README section structure by @jeffersongoncalves in https://github.com/jeffersongoncalves/filament-oidc/pull/4
* chore: add GitHub Sponsors to FUNDING.yml by @jeffersongoncalves in https://github.com/jeffersongoncalves/filament-oidc/pull/5
* ci: standardize update-changelog workflow (1.x) by @jeffersongoncalves in https://github.com/jeffersongoncalves/filament-oidc/pull/6
* ci: standardize dependabot config by @jeffersongoncalves in https://github.com/jeffersongoncalves/filament-oidc/pull/7
* ci: standardize tests workflow (1.x) by @jeffersongoncalves in https://github.com/jeffersongoncalves/filament-oidc/pull/8
* feat(i18n): add translations (1.x) by @jeffersongoncalves in https://github.com/jeffersongoncalves/filament-oidc/pull/9

### New Contributors

* @jeffersongoncalves made their first contribution in https://github.com/jeffersongoncalves/filament-oidc/pull/3

**Full Changelog**: https://github.com/jeffersongoncalves/filament-oidc/compare/v1.0.0...1.1.0

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
