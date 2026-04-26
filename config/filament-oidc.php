<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default guard
    |--------------------------------------------------------------------------
    |
    | The authentication guard used when logging the resolved user in. Each
    | panel may override this on the plugin instance via ->guard(...).
    |
    */
    'guard' => env('OIDC_GUARD', 'web'),

    /*
    |--------------------------------------------------------------------------
    | User model
    |--------------------------------------------------------------------------
    |
    | The Eloquent model used when provisioning users from OIDC claims. Each
    | panel may override this on the plugin instance via ->userModel(...).
    |
    */
    'user_model' => env('OIDC_USER_MODEL', 'App\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | OIDC identities table
    |--------------------------------------------------------------------------
    |
    | Where the polymorphic mapping between an authenticatable and its OIDC
    | identities (issuer + subject) is stored. Adjust the table name when
    | publishing the migration if you need a different name.
    |
    */
    'identities_table' => 'oidc_identities',

    /*
    |--------------------------------------------------------------------------
    | Auto-create users
    |--------------------------------------------------------------------------
    |
    | When enabled the plugin will create a new user (and link a new identity)
    | the first time an unknown OIDC subject signs in. Disable this for
    | enterprise scenarios where users must be provisioned out of band.
    |
    */
    'auto_create_users' => true,

    /*
    |--------------------------------------------------------------------------
    | Logout from the IdP
    |--------------------------------------------------------------------------
    |
    | When enabled the package will redirect to the IdP `end_session_endpoint`
    | (RP-initiated logout) after logging the user out locally.
    |
    */
    'logout_from_idp' => false,

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | The plugin registers its routes inside each panel route group. Paths are
    | relative to the panel path. Final route names follow Filament's panel
    | naming, e.g. `filament.{panel-id}.oidc.callback`.
    |
    */
    'route' => [
        'redirect_path' => 'oidc/redirect',
        'callback_path' => 'oidc/callback',
        'logout_path' => 'oidc/logout',
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Login button
    |--------------------------------------------------------------------------
    |
    | Customizes the button rendered on the panel login page. The label falls
    | back to the package translation files when null. The position controls
    | whether the button is rendered before or after the standard login form.
    |
    */
    'button' => [
        'label' => null,
        'icon' => 'heroicon-o-shield-check',
        'position' => 'after',
    ],
];
