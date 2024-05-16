<?php

namespace Northrook\Symfony\Core\Facade;

use Northrook\Symfony\Core\DependencyInjection\Facade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

final class URL extends Facade
{

    /**
     * Generates a URL or path for a specific route based on the given parameters.
     *
     * Uses {@see UrlGeneratorInterface::generate()} to generate the URL.
     *
     * @param string  $name
     * @param array   $parameters
     * @param int     $referenceType
     *
     * @return string
     */
    public static function generate(
        string $name,
        array  $parameters = [],
        int    $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
    ) : string {
        return URL::getService( RouterInterface::class )
                  ->generate( $name, $parameters, $referenceType );
    }

    public static function current() : string {
        return URL::getService( RouterInterface::class )->getContext()->getBaseUrl();
    }
    
    /**
     * Determine if the given path is a valid URL.
     *
     * @param string  $url
     * @param bool    $enforceHttps    Whether to enforce HTTPS.
     * @param bool    $logInsecureUrl  Whether to log insecure URLs.
     *
     * @return bool
     */
    public static function isValid( string $url, bool $enforceHttps = false, bool $logInsecureUrl = true ) : bool {

        if ( str_starts_with( $url, 'http://' ) ) {
            if ( $logInsecureUrl ) {
                Logger::log(
                    $enforceHttps ? 'error' : 'warning',
                    'The URL {url} is not secure. Please use https:// instead.',
                    [ 'url' => $url ],
                );
            }
            if ( $enforceHttps ) {
                return false;
            }
        }

        if ( str_starts_with( $url, 'http://' ) ||
             str_starts_with( $url, 'https://' ) ||
             str_starts_with( $url, 'mailto:' ) ||
             str_starts_with( $url, 'tel:' ) ||
             str_starts_with( $url, '#' )
        ) {
            return filter_var( $url, FILTER_VALIDATE_URL );
        }

        return false;
    }

}