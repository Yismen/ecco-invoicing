<?php

namespace App\Services;

use Jeffgreco13\FilamentBreezy\BreezyCore;

class BreezeCoreService
{
    public static function make(): BreezyCore
    {
        return BreezyCore::make()
            ->myProfile(
                shouldRegisterUserMenu: true, // Sets the 'account' link in the panel User Menu (default = true)
                userMenuLabel: 'My Profile', // Customizes the 'account' link label in the panel User Menu (default = null)
                shouldRegisterNavigation: false, // Adds a main navigation item for the My Profile page (default = false)
                navigationGroup: 'Settings', // Sets the navigation group for the My Profile page (default = null)
                hasAvatars: false, // Enables the avatar upload form component (default = false)
                slug: 'my-profile' // Sets the slug for the profile page (default = 'my-profile')
            );
    }
}
