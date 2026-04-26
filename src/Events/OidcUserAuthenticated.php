<?php

namespace JeffersonGoncalves\Filament\Oidc\Events;

use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use JeffersonGoncalves\Filament\Oidc\Models\OidcIdentity;
use Laravel\Socialite\Two\User as SocialiteUser;

class OidcUserAuthenticated
{
    use Dispatchable;

    public function __construct(
        public Model $user,
        public OidcIdentity $identity,
        public SocialiteUser $oidcUser,
        public Panel $panel,
    ) {}
}
