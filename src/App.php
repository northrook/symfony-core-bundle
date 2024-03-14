<?php

namespace Northrook\Symfony\Core;

use JetBrains\PhpStorm\ExpectedValues;

final class App extends SymfonyCoreFacade
{
    /**
     * @param string  $is
     *
     * @return bool
     * @todo 'public' functionality reads the Site Settings Entity, but this is not yet implemented
     *
     */
    public static function env(
        #[ExpectedValues( [ 'dev', 'prod', 'debug', 'public' ] )]
        string $is,
    ) : bool {
        return match ( $is ) {
            'dev'   => App::kernel()->getEnvironment() == 'dev',
            'prod'  => App::kernel()->getEnvironment() == 'prod',
            'debug' => App::kernel()->isDebug(),
//            'public' => Settings::site()->isPublic, // Check if global is public, then check if current route is public
            default => false,
        };
    }
}