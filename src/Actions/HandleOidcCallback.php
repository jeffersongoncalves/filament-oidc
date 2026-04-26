<?php

namespace JeffersonGoncalves\Filament\Oidc\Actions;

use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use JeffersonGoncalves\Filament\Oidc\Events\OidcUserAuthenticated;
use JeffersonGoncalves\Filament\Oidc\Events\OidcUserCreated;
use JeffersonGoncalves\Filament\Oidc\Exceptions\OidcAuthenticationException;
use JeffersonGoncalves\Filament\Oidc\FilamentOidcPlugin;
use JeffersonGoncalves\Filament\Oidc\Models\OidcIdentity;
use JeffersonGoncalves\LaravelOidc\Data\OidcUser;
use Laravel\Socialite\Two\User as SocialiteUser;

class HandleOidcCallback
{
    public function __invoke(SocialiteUser $oidcUser, FilamentOidcPlugin $plugin, Panel $panel): Model
    {
        $issuer = $this->resolveIssuer($oidcUser, $plugin);
        $subject = (string) $oidcUser->getId();

        return DB::transaction(function () use ($oidcUser, $plugin, $panel, $issuer, $subject): Model {
            $identity = OidcIdentity::query()
                ->where('issuer', $issuer)
                ->where('subject', $subject)
                ->first();

            $user = $identity?->authenticatable;

            if (! $user instanceof Model) {
                $user = $this->resolveOrCreateUser($oidcUser, $plugin, $panel);
            }

            $identity = $this->upsertIdentity($identity, $user, $oidcUser, $issuer, $subject);

            OidcUserAuthenticated::dispatch($user, $identity, $oidcUser, $panel);

            return $user;
        });
    }

    protected function resolveOrCreateUser(SocialiteUser $oidcUser, FilamentOidcPlugin $plugin, Panel $panel): Model
    {
        $modelClass = $plugin->getUserModel();

        if ($resolver = $plugin->getResolveUserUsing()) {
            $user = $resolver(new $modelClass, $oidcUser);

            if (! $user->exists) {
                $user->save();
            }

            return $user;
        }

        $email = $oidcUser->getEmail();

        $user = $email !== null
            ? $modelClass::query()->where('email', $email)->first()
            : null;

        if ($user instanceof Model) {
            return $user;
        }

        if (! $plugin->shouldAutoCreateUsers()) {
            throw OidcAuthenticationException::autoCreateDisabled();
        }

        $attributes = $this->resolveUserAttributes($oidcUser, $plugin);

        $user = $modelClass::query()->create($attributes);

        if (! $user instanceof Model) {
            throw OidcAuthenticationException::callbackFailed();
        }

        OidcUserCreated::dispatch($user, $oidcUser, $panel);

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveUserAttributes(SocialiteUser $oidcUser, FilamentOidcPlugin $plugin): array
    {
        if ($mapper = $plugin->getUserAttributesUsing()) {
            return $mapper($oidcUser);
        }

        return array_filter([
            'name' => $oidcUser->getName() ?? $oidcUser->getNickname() ?? $oidcUser->getEmail(),
            'email' => $oidcUser->getEmail(),
            'password' => bcrypt(bin2hex(random_bytes(16))),
        ], fn ($value) => $value !== null);
    }

    protected function upsertIdentity(
        ?OidcIdentity $identity,
        Model $user,
        SocialiteUser $oidcUser,
        string $issuer,
        string $subject,
    ): OidcIdentity {
        $payload = [
            'authenticatable_type' => $user->getMorphClass(),
            'authenticatable_id' => $user->getKey(),
            'issuer' => $issuer,
            'subject' => $subject,
            'email' => $oidcUser->getEmail(),
            'name' => $oidcUser->getName(),
            'claims' => $this->extractClaims($oidcUser),
            'id_token' => $this->extractIdToken($oidcUser),
            'access_token' => $oidcUser->token,
            'refresh_token' => $oidcUser->refreshToken,
            'expires_at' => $oidcUser->expiresIn ? now()->addSeconds((int) $oidcUser->expiresIn) : null,
        ];

        if ($identity === null) {
            return OidcIdentity::query()->create($payload);
        }

        $identity->fill($payload)->save();

        return $identity;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function extractClaims(SocialiteUser $oidcUser): ?array
    {
        if ($oidcUser instanceof OidcUser && $oidcUser->idTokenClaims !== []) {
            return $oidcUser->idTokenClaims;
        }

        $raw = $oidcUser->getRaw();

        return $raw === [] ? null : $raw;
    }

    protected function extractIdToken(SocialiteUser $oidcUser): ?string
    {
        if ($oidcUser instanceof OidcUser) {
            return $oidcUser->idToken;
        }

        return null;
    }

    protected function resolveIssuer(SocialiteUser $oidcUser, FilamentOidcPlugin $plugin): string
    {
        if ($oidcUser instanceof OidcUser) {
            $iss = $oidcUser->idTokenClaims['iss'] ?? null;

            if (is_string($iss) && $iss !== '') {
                return $iss;
            }
        }

        return (string) ($plugin->getIssuerUrl() ?? 'unknown');
    }
}
