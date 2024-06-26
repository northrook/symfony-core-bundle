<?php

namespace Northrook\Symfony\Core\Facade;

use JetBrains\PhpStorm\ExpectedValues;

final class Settings
{
    private const PUBLIC_SETTINGS = [
        'security',
        'security.registration',
        'security.password_reset',
        'security.email_verification',
        'security.admin',
        'url',
        'url.trailingSlash',
        'url.enforceHttps',
        'url.log.insecureUrl',
        'url.log.404',
    ];


    public static function get(
        string $setting,
    ) : mixed {
        return false;
    }

    // Global Settings
    public static function app() : self {
        return Settings::config();
    }


    /**
     * @param string|null  $get  Retrieve setting by `dot.notation`
     *
     * @return mixed
     */
    public static function public(
        #[ExpectedValues( Settings::PUBLIC_SETTINGS )]
        string $get = null,
    ) : mixed {
        return (bool) $get;
    }

    // User specific Settings
    public static function user() : self {
        return Settings::config();
    }

    // Admin specific Settings
    public static function admin() : self {
        return Settings::config();
    }
}