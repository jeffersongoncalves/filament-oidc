<?php

namespace JeffersonGoncalves\Filament\Oidc\Exceptions;

use RuntimeException;

class OidcAuthenticationException extends RuntimeException
{
    public static function userNotFound(): self
    {
        return new self(__('filament-oidc::filament-oidc.errors.user_not_found'));
    }

    public static function autoCreateDisabled(): self
    {
        return new self(__('filament-oidc::filament-oidc.errors.auto_create_disabled'));
    }

    public static function callbackFailed(?\Throwable $previous = null): self
    {
        return new self(
            __('filament-oidc::filament-oidc.errors.callback_failed'),
            previous: $previous,
        );
    }
}
