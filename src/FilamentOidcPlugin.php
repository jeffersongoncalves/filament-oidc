<?php

namespace JeffersonGoncalves\Filament\Oidc;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use JeffersonGoncalves\Filament\Oidc\Http\Controllers\OidcController;
use Laravel\Socialite\Two\User;

class FilamentOidcPlugin implements Plugin
{
    protected ?string $guard = null;

    protected ?string $userModel = null;

    protected ?string $issuerUrl = null;

    protected ?string $clientId = null;

    protected ?string $clientSecret = null;

    /**
     * @var array<int, string>|null
     */
    protected ?array $scopes = null;

    protected ?bool $autoCreateUsers = null;

    protected ?bool $logoutFromIdp = null;

    protected ?string $buttonLabel = null;

    protected ?string $buttonIcon = null;

    protected ?string $buttonPosition = null;

    /** @var (Closure(User): array<string, mixed>)|null */
    protected ?Closure $userAttributesUsing = null;

    /** @var (Closure(Model, User): Model)|null */
    protected ?Closure $resolveUserUsing = null;

    /** @var (Closure(Panel): string)|null */
    protected ?Closure $redirectAfterLogin = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'filament-oidc';
    }

    public function register(Panel $panel): void
    {
        $panel->routes(function () {
            Route::middleware(config('filament-oidc.route.middleware', ['web']))
                ->group(function () {
                    Route::get(
                        config('filament-oidc.route.redirect_path', 'oidc/redirect'),
                        [OidcController::class, 'redirect']
                    )->name('oidc.redirect');

                    Route::get(
                        config('filament-oidc.route.callback_path', 'oidc/callback'),
                        [OidcController::class, 'callback']
                    )->name('oidc.callback');

                    Route::post(
                        config('filament-oidc.route.logout_path', 'oidc/logout'),
                        [OidcController::class, 'logout']
                    )->name('oidc.logout');
                });
        });

        $hook = $this->getButtonPosition() === 'before'
            ? PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE
            : PanelsRenderHook::AUTH_LOGIN_FORM_AFTER;

        $panel->renderHook(
            $hook,
            fn (): View => app(ViewFactory::class)->make('filament-oidc::login-button', [
                'panel' => $panel,
                'plugin' => $this,
            ])
        );
    }

    public function boot(Panel $panel): void {}

    public function guard(string $guard): static
    {
        $this->guard = $guard;

        return $this;
    }

    public function getGuard(): string
    {
        return $this->guard ?? config('filament-oidc.guard', 'web');
    }

    public function userModel(string $userModel): static
    {
        $this->userModel = $userModel;

        return $this;
    }

    public function getUserModel(): string
    {
        /** @var class-string<Model> $model */
        $model = $this->userModel ?? config('filament-oidc.user_model', 'App\\Models\\User');

        return $model;
    }

    public function issuerUrl(?string $issuerUrl): static
    {
        $this->issuerUrl = $issuerUrl;

        return $this;
    }

    public function getIssuerUrl(): ?string
    {
        return $this->issuerUrl ?? config('oidc.default.issuer_url');
    }

    public function clientId(?string $clientId): static
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getClientId(): ?string
    {
        return $this->clientId ?? config('oidc.default.client_id');
    }

    public function clientSecret(?string $clientSecret): static
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret ?? config('oidc.default.client_secret');
    }

    /**
     * @param  array<int, string>|null  $scopes
     */
    public function scopes(?array $scopes): static
    {
        $this->scopes = $scopes;

        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function getScopes(): array
    {
        if ($this->scopes !== null) {
            return $this->scopes;
        }

        $configured = config('oidc.default.scopes');

        if (is_array($configured) && $configured !== []) {
            return array_values(array_map('strval', $configured));
        }

        return ['openid', 'profile', 'email'];
    }

    public function autoCreateUsers(bool $condition = true): static
    {
        $this->autoCreateUsers = $condition;

        return $this;
    }

    public function shouldAutoCreateUsers(): bool
    {
        return $this->autoCreateUsers ?? (bool) config('filament-oidc.auto_create_users', true);
    }

    public function logoutFromIdp(bool $condition = true): static
    {
        $this->logoutFromIdp = $condition;

        return $this;
    }

    public function shouldLogoutFromIdp(): bool
    {
        return $this->logoutFromIdp ?? (bool) config('filament-oidc.logout_from_idp', false);
    }

    public function buttonLabel(?string $label): static
    {
        $this->buttonLabel = $label;

        return $this;
    }

    public function getButtonLabel(): string
    {
        return $this->buttonLabel
            ?? config('filament-oidc.button.label')
            ?? __('filament-oidc::filament-oidc.button.label');
    }

    public function buttonIcon(?string $icon): static
    {
        $this->buttonIcon = $icon;

        return $this;
    }

    public function getButtonIcon(): ?string
    {
        return $this->buttonIcon ?? config('filament-oidc.button.icon', 'heroicon-o-shield-check');
    }

    public function position(string $position): static
    {
        $this->buttonPosition = $position;

        return $this;
    }

    public function getButtonPosition(): string
    {
        return $this->buttonPosition ?? config('filament-oidc.button.position', 'after');
    }

    /**
     * @param  Closure(User): array<string, mixed>  $callback
     */
    public function userAttributesUsing(Closure $callback): static
    {
        $this->userAttributesUsing = $callback;

        return $this;
    }

    /**
     * @return (Closure(User): array<string, mixed>)|null
     */
    public function getUserAttributesUsing(): ?Closure
    {
        return $this->userAttributesUsing;
    }

    /**
     * @param  Closure(Model, User): Model  $callback
     */
    public function resolveUserUsing(Closure $callback): static
    {
        $this->resolveUserUsing = $callback;

        return $this;
    }

    /**
     * @return (Closure(Model, User): Model)|null
     */
    public function getResolveUserUsing(): ?Closure
    {
        return $this->resolveUserUsing;
    }

    /**
     * @param  Closure(Panel): string  $callback
     */
    public function redirectAfterLogin(Closure $callback): static
    {
        $this->redirectAfterLogin = $callback;

        return $this;
    }

    /**
     * @return (Closure(Panel): string)|null
     */
    public function getRedirectAfterLogin(): ?Closure
    {
        return $this->redirectAfterLogin;
    }
}
