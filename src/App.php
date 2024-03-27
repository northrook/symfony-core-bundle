<?php

namespace Northrook\Symfony\Core;

use JetBrains\PhpStorm\ExpectedValues;
use Northrook\Logger\Log;

final class App extends SymfonyCoreFacade
{

    /**
     * @param string  $is
     *
     * @return bool
     * @todo 'public' functionality reads the Site Settings Entity, but this is not yet implemented
     */
    public static function env(
        #[ExpectedValues( [ 'dev', 'prod', 'debug', 'public' ] )]
        string $is,
    ) : bool {
        return match ( $is ) {
            'dev'   => self::getKernel()->getEnvironment() == 'dev',
            'prod'  => self::getKernel()->getEnvironment() == 'prod',
            'debug' => self::getKernel()->isDebug(),
//            'public' => Settings::site()->isPublic, // Check if global is public, then check if current route is public
            default => false,
        };
    }

    public static function baseUrl( ?string $append = null ) : string {
        $url = rtrim( Request::currentRequest()->getSchemeAndHttpHost(), '/' ) . '/';
        if ( $append ) {
            $url .= ltrim( str_replace( '\\', '/', $append ), '/' );
        }
        if ( !filter_var( $url, FILTER_VALIDATE_URL ) ) {
            Log::Error( 'Invalid URL: {url}', [ 'url' => $url ] );
        }
        return $url;
    }
}