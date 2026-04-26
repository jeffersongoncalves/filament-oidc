<?php

use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Mockery as M;

afterEach(function (): void {
    M::close();
});

it('redirects to the IdP authorization endpoint', function (): void {
    $driver = M::mock(Provider::class);
    $driver->shouldReceive('setConfig')->andReturnSelf();
    $driver->shouldReceive('redirectUrl')->andReturnSelf();
    $driver->shouldReceive('scopes')->andReturnSelf();
    $driver->shouldReceive('redirect')->andReturn(new RedirectResponse('https://idp.test/authorize?foo=bar'));

    Socialite::shouldReceive('driver')->with('oidc')->andReturn($driver);

    $response = $this->get(route('filament.admin.oidc.redirect'));

    $response->assertRedirect('https://idp.test/authorize?foo=bar');
});
