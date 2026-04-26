<?php

namespace JeffersonGoncalves\Filament\Oidc\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use JeffersonGoncalves\Filament\Oidc\Concerns\HasOidcIdentities;

class User extends Authenticatable
{
    use HasOidcIdentities;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
