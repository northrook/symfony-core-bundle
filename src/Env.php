<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core;

final class Env {

    private static bool $debug = false;
    private static string $environment = 'dev';

    public function __construct(
        string $env,
        bool $debug,
    ) {
        Env::$environment = $env;
        Env::$debug = $debug;
    }

    public static function isProduction(): bool {
        return Env::$environment === 'prod';
    }

    public static function isDevelopment(): bool {
        return Env::$environment === 'dev';
    }

    public static function isStaging(): bool {
        return Env::$environment === 'staging';
    }

    public static function isDebug(): bool {
        return Env::$debug;
    }
}