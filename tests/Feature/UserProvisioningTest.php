<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use JeffersonGoncalves\Filament\Oidc\Tests\Fixtures\User;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Mockery as M;

afterEach(function (): void {
    M::close();
});

it('blocks new users when auto provisioning is disabled', function (): void {
    Filament::getPanel('admin')->getPlugin('filament-oidc')->autoCreateUsers(false);

    $driver = M::mock(Provider::class);
    $driver->shouldReceive('setConfig')->andReturnSelf();
    $driver->shouldReceive('redirectUrl')->andReturnSelf();
    $driver->shouldReceive('scopes')->andReturnSelf();
    $driver->shouldReceive('user')->andReturn(fakeOidcUser());

    Socialite::shouldReceive('driver')->with('oidc')->andReturn($driver);

    $response = $this->get(route('filament.admin.oidc.callback'));

    $response->assertRedirect();

    expect(User::query()->count())->toBe(0);
    expect(Auth::guard('web')->check())->toBeFalse();

    Filament::getPanel('admin')->getPlugin('filament-oidc')->autoCreateUsers(true);
});

it('respects a custom user attribute mapper', function (): void {
    Filament::getPanel('admin')
        ->getPlugin('filament-oidc')
        ->userAttributesUsing(fn ($oidcUser) => [
            'name' => mb_strtoupper($oidcUser->getName()),
            'email' => $oidcUser->getEmail(),
            'password' => bcrypt('secret'),
        ]);

    $driver = M::mock(Provider::class);
    $driver->shouldReceive('setConfig')->andReturnSelf();
    $driver->shouldReceive('redirectUrl')->andReturnSelf();
    $driver->shouldReceive('scopes')->andReturnSelf();
    $driver->shouldReceive('user')->andReturn(fakeOidcUser());

    Socialite::shouldReceive('driver')->with('oidc')->andReturn($driver);

    $this->get(route('filament.admin.oidc.callback'));

    expect(User::query()->first()->name)->toBe('JANE DOE');
});
