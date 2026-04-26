<?php

use Filament\Facades\Filament;
use JeffersonGoncalves\Filament\Oidc\FilamentOidcPlugin;

it('registers the plugin on the test panel', function (): void {
    $panel = Filament::getPanel('admin');

    expect($panel->getPlugin('filament-oidc'))->toBeInstanceOf(FilamentOidcPlugin::class);
});

it('registers the OIDC routes within the panel scope', function (): void {
    expect(route('filament.admin.oidc.redirect'))->toContain('/admin/oidc/redirect');
    expect(route('filament.admin.oidc.callback'))->toContain('/admin/oidc/callback');
    expect(route('filament.admin.oidc.logout'))->toContain('/admin/oidc/logout');
});

it('renders the login button view with the panel redirect URL', function (): void {
    $panel = Filament::getPanel('admin');

    $rendered = view('filament-oidc::login-button', [
        'panel' => $panel,
        'plugin' => $panel->getPlugin('filament-oidc'),
    ])->render();

    expect($rendered)->toContain(route('filament.admin.oidc.redirect'));
    expect($rendered)->toContain(__('filament-oidc::filament-oidc.button.label'));
});

it('exposes a fluent api with sane defaults', function (): void {
    $plugin = FilamentOidcPlugin::make();

    expect($plugin->getId())->toBe('filament-oidc');
    expect($plugin->getGuard())->toBe('web');
    expect($plugin->shouldAutoCreateUsers())->toBeTrue();
    expect($plugin->shouldLogoutFromIdp())->toBeFalse();
    expect($plugin->getButtonPosition())->toBe('after');
    expect($plugin->getScopes())->toEqual(['openid', 'email', 'profile']);
});

it('lets each panel override the guard and auto-create flag', function (): void {
    $plugin = FilamentOidcPlugin::make()
        ->guard('admins')
        ->autoCreateUsers(false)
        ->logoutFromIdp(true)
        ->position('before');

    expect($plugin->getGuard())->toBe('admins');
    expect($plugin->shouldAutoCreateUsers())->toBeFalse();
    expect($plugin->shouldLogoutFromIdp())->toBeTrue();
    expect($plugin->getButtonPosition())->toBe('before');
});
