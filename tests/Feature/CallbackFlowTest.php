<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use JeffersonGoncalves\Filament\Oidc\Events\OidcUserAuthenticated;
use JeffersonGoncalves\Filament\Oidc\Events\OidcUserCreated;
use JeffersonGoncalves\Filament\Oidc\Models\OidcIdentity;
use JeffersonGoncalves\Filament\Oidc\Tests\Fixtures\User;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery as M;

afterEach(function (): void {
    M::close();
});

function fakeOidcUser(array $overrides = []): SocialiteUser
{
    $user = new SocialiteUser;
    $user->id = $overrides['id'] ?? 'sub-123';
    $user->name = $overrides['name'] ?? 'Jane Doe';
    $user->email = $overrides['email'] ?? 'jane@example.com';
    $user->token = $overrides['token'] ?? 'access-token';
    $user->refreshToken = $overrides['refresh_token'] ?? 'refresh-token';
    $user->expiresIn = $overrides['expires_in'] ?? 3600;
    $user->setRaw($overrides['raw'] ?? ['sub' => $user->id, 'iss' => 'https://idp.test']);

    /** @phpstan-ignore-next-line dynamic property mirroring laravel-oidc */
    $user->idTokenClaims = $overrides['claims'] ?? ['iss' => 'https://idp.test', 'sub' => $user->id];
    /** @phpstan-ignore-next-line dynamic property mirroring laravel-oidc */
    $user->idToken = $overrides['id_token'] ?? 'header.payload.sig';

    return $user;
}

function mockOidcDriver(SocialiteUser $user): void
{
    $driver = M::mock(Provider::class);
    $driver->shouldReceive('setConfig')->andReturnSelf();
    $driver->shouldReceive('redirectUrl')->andReturnSelf();
    $driver->shouldReceive('scopes')->andReturnSelf();
    $driver->shouldReceive('user')->andReturn($user);

    Socialite::shouldReceive('driver')->with('oidc')->andReturn($driver);
}

it('logs the resolved user in and redirects to the panel', function (): void {
    Event::fake([OidcUserAuthenticated::class, OidcUserCreated::class]);

    mockOidcDriver(fakeOidcUser());

    $response = $this->get(route('filament.admin.oidc.callback'));

    $response->assertRedirect('/admin');

    expect(Auth::guard('web')->check())->toBeTrue();
    expect(Auth::guard('web')->user()->email)->toBe('jane@example.com');

    expect(OidcIdentity::query()->count())->toBe(1);
    expect(OidcIdentity::query()->first()->subject)->toBe('sub-123');

    Event::assertDispatched(OidcUserAuthenticated::class);
    Event::assertDispatched(OidcUserCreated::class);
});

it('reuses an existing identity on subsequent logins', function (): void {
    $existing = User::query()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'irrelevant',
    ]);

    OidcIdentity::query()->create([
        'authenticatable_type' => $existing->getMorphClass(),
        'authenticatable_id' => $existing->getKey(),
        'issuer' => 'https://idp.test',
        'subject' => 'sub-123',
    ]);

    Event::fake([OidcUserCreated::class]);

    mockOidcDriver(fakeOidcUser());

    $this->get(route('filament.admin.oidc.callback'))->assertRedirect('/admin');

    expect(User::query()->count())->toBe(1);
    expect(OidcIdentity::query()->count())->toBe(1);
    expect(Auth::guard('web')->id())->toBe($existing->getKey());

    Event::assertNotDispatched(OidcUserCreated::class);
});

it('links a new OIDC identity to an existing user matched by email', function (): void {
    $existing = User::query()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'irrelevant',
    ]);

    Event::fake([OidcUserCreated::class]);

    mockOidcDriver(fakeOidcUser());

    $this->get(route('filament.admin.oidc.callback'))->assertRedirect('/admin');

    expect(User::query()->count())->toBe(1);
    expect(OidcIdentity::query()->count())->toBe(1);
    expect(OidcIdentity::query()->first()->authenticatable_id)->toBe($existing->getKey());

    Event::assertNotDispatched(OidcUserCreated::class);
});
