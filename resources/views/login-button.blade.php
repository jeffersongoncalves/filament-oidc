@php
    /** @var \Filament\Panel $panel */
    /** @var \JeffersonGoncalves\Filament\Oidc\FilamentOidcPlugin $plugin */
    $redirectUrl = route("filament.{$panel->getId()}.oidc.redirect");
@endphp

<div class="fi-oidc-login-button mt-4">
    <x-filament::button
        tag="a"
        :href="$redirectUrl"
        color="primary"
        outlined
        class="w-full"
    >
        {{ $plugin->getButtonLabel() }}
    </x-filament::button>
</div>
