<?php

namespace JeffersonGoncalves\Filament\Oidc\Events;

use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Laravel\Socialite\Two\User as SocialiteUser;

class OidcUserCreated
{
    use Dispatchable;

    public function __construct(
        public Model $user,
        public SocialiteUser $oidcUser,
        public Panel $panel,
    ) {}
}
