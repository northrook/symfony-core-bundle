<?php

namespace Northrook\Symfony\Core;

use Northrook\Core\Config;

final class Settings extends Config
{

    // Global Settings
    public static function app() : self {
        return Settings::config();
    }

    // Public Settings
    public static function site() : self {
        return Settings::config();
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