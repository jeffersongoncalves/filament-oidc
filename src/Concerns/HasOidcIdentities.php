<?php

namespace JeffersonGoncalves\Filament\Oidc\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use JeffersonGoncalves\Filament\Oidc\Models\OidcIdentity;

/**
 * @mixin Model
 */
trait HasOidcIdentities
{
    public function oidcIdentities(): MorphMany
    {
        return $this->morphMany(OidcIdentity::class, 'authenticatable');
    }
}
