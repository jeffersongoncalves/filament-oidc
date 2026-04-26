<?php

use Illuminate\Support\Facades\Auth;
use JeffersonGoncalves\Filament\Oidc\Tests\Fixtures\User;

it('logs the user out and redirects to the login screen', function (): void {
    $user = User::query()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'irrelevant',
    ]);

    Auth::guard('web')->login($user);

    $response = $this->post(route('filament.admin.oidc.logout'));

    $response->assertRedirect();
    expect(Auth::guard('web')->check())->toBeFalse();
});
