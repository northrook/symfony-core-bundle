<?php

namespace Northrook\Symfony\Core;

final class Settings
{

    // Global Settings
    public static function app() : self {
        return new self();
    }

    // Public Settings
    public static function site() : self {
        return new self();
    }

    // User specific Settings
    public static function user() : self {
        return new self();
    }

    // Admin specific Settings
    public static function admin() : self {
        return new self();
    }
}