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
        #[ExpectedValues( [ 'dev', 'prod', 'debug', 'public', null ] )]
        ?string $is = null,
    ) : bool | object {

        $environment = [
            'dev'   => App::getKernel()->getEnvironment() == 'dev',
            'prod'  => App::getKernel()->getEnvironment() == 'prod',
            'debug' => App::getKernel()->isDebug(),
            // Check if global is public, then check if current route is public
            // 'public' => Settings::site()->isPublic,
        ];

        return $is ? $environment[ $is ] ?? false : (object) $environment;

//        return $environment[ $is ] ?? (object) $environment;

//        return match ( $is ) {
//            'dev'   => App::getKernel()->getEnvironment() == 'dev',
//            'prod'  => App::getKernel()->getEnvironment() == 'prod',
//            'debug' => App::getKernel()->isDebug(),
////            'public' => Settings::site()->isPublic, // Check if global is public, then check if current route is public
//            default => false,
//        };
    }
}