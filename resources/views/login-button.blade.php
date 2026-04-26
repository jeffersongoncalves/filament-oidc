@php
    /** @var \Filament\Panel $panel */
    /** @var \JeffersonGoncalves\Filament\Oidc\FilamentOidcPlugin $plugin */
    $redirectUrl = route("filament.{$panel->getId()}.oidc.redirect");
@endphp

<div class="fi-oidc-login-button mt-4">
    <a
        href="{{ $redirectUrl }}"
        class="fi-btn fi-btn-color-primary fi-btn-size-md fi-btn-variant-outlined relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg w-full px-3 py-2 gap-1.5 inline-grid"
    >
        <span class="fi-btn-label">
            {{ $plugin->getButtonLabel() }}
        </span>
    </a>
</div>
