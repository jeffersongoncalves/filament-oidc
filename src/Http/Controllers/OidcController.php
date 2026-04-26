<?php

namespace JeffersonGoncalves\Filament\Oidc\Http\Controllers;

use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Panel;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use JeffersonGoncalves\Filament\Oidc\Actions\HandleOidcCallback;
use JeffersonGoncalves\Filament\Oidc\Exceptions\OidcAuthenticationException;
use JeffersonGoncalves\Filament\Oidc\FilamentOidcPlugin;
use JeffersonGoncalves\Filament\Oidc\Support\SocialiteConfigurator;
use JeffersonGoncalves\LaravelOidc\Facades\Oidc;
use Laravel\Socialite\Two\User;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class OidcController extends Controller
{
    public function redirect(): Response
    {
        $panel = $this->panel();

        return SocialiteConfigurator::for($panel, $this->plugin($panel))->redirect();
    }

    public function callback(Request $request, HandleOidcCallback $handler): RedirectResponse
    {
        $panel = $this->panel();
        $plugin = $this->plugin($panel);

        try {
            $oidcUser = SocialiteConfigurator::for($panel, $plugin)->user();

            if (! $oidcUser instanceof User) {
                throw OidcAuthenticationException::callbackFailed();
            }

            $user = $handler($oidcUser, $plugin, $panel);

            if (! $user instanceof Authenticatable) {
                throw OidcAuthenticationException::callbackFailed();
            }
        } catch (OidcAuthenticationException $exception) {
            Notification::make()
                ->title($exception->getMessage())
                ->danger()
                ->send();

            return redirect()->to($panel->getLoginUrl() ?? $panel->getUrl());
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title(__('filament-oidc::filament-oidc.errors.callback_failed'))
                ->danger()
                ->send();

            return redirect()->to($panel->getLoginUrl() ?? $panel->getUrl());
        }

        Auth::guard($plugin->getGuard())->login($user, remember: true);

        $request->session()->regenerate();

        $target = $plugin->getRedirectAfterLogin();

        return redirect()->to($target !== null ? $target($panel) : $panel->getUrl());
    }

    public function logout(Request $request): RedirectResponse
    {
        $panel = $this->panel();
        $plugin = $this->plugin($panel);

        Auth::guard($plugin->getGuard())->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($plugin->shouldLogoutFromIdp()) {
            $endSessionUrl = $this->resolveEndSessionUrl($plugin, $panel);

            if ($endSessionUrl !== null) {
                return redirect()->away($endSessionUrl);
            }
        }

        return redirect()->to($panel->getLoginUrl() ?? $panel->getUrl());
    }

    protected function panel(): Panel
    {
        $panel = Filament::getCurrentPanel();

        if ($panel === null) {
            throw new \LogicException('No active Filament panel could be resolved for the OIDC route.');
        }

        return $panel;
    }

    protected function plugin(Panel $panel): FilamentOidcPlugin
    {
        /** @var FilamentOidcPlugin $plugin */
        $plugin = $panel->getPlugin('filament-oidc');

        return $plugin;
    }

    protected function resolveEndSessionUrl(FilamentOidcPlugin $plugin, Panel $panel): ?string
    {
        $issuer = $plugin->getIssuerUrl();

        if ($issuer === null) {
            return null;
        }

        try {
            $discovery = Oidc::discover($issuer);
        } catch (Throwable) {
            return null;
        }

        $endpoint = $discovery->endSessionEndpoint;

        if ($endpoint === null || $endpoint === '') {
            return null;
        }

        $postLogoutRedirect = config('filament-oidc.post_logout_redirect_uri')
            ?? $panel->getLoginUrl()
            ?? $panel->getUrl();

        $separator = str_contains($endpoint, '?') ? '&' : '?';

        return $endpoint.$separator.http_build_query([
            'client_id' => $plugin->getClientId(),
            'post_logout_redirect_uri' => $postLogoutRedirect,
        ]);
    }
}
