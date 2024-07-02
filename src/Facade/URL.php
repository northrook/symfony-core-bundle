<?php

namespace Northrook\Symfony\Core\Facade;

use Northrook\Symfony\Core\DependencyInjection\Facade;
use Northrook\Symfony\Core\Settings\Setting;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;


/**
 * - Add support for {@see Setting\URL}.
 *
 */


/**
 * @see UrlGeneratorInterface, RouterInterface
 */
final class URL extends Facade
{
    /**
     * Get the URL or path for a specific route based on the given parameters.
     *
     * Uses {@see UrlGeneratorInterface::generate()} to generate the URL.
     *
     * @param string  $route
     * @param array   $parameters
     * @param int     $referenceType
     *
     * @return string
     */
    public static function get(
        string $route,
        array  $parameters = [],
        int    $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
    ) : string {
        return URL::getService( RouterInterface::class )
                  ->generate( $route, $parameters, $referenceType );
    }

    /**
     * Returns the base URL.
     *
     * @param null|string  $append
     * @param bool         $absolute
     *
     * @return string
     *
     * @todo This needs to retrieve the full URL, not just the base path by default.
     *
     */
    public static function base( ?string $append = null, bool $absolute = false ) : string {
        $url = URL::getService( RouterInterface::class )->getContext()->getBaseUrl();

        if ( $append ) {
            $url .= '/' . trim( $append, '/' );
        }

        return $url;
    }

    /**
     * Returns the current URL.
     *
     * @param null|string  $append
     *
     * @return string
     */
    public static function current( ?string $append = null ) : string {
        $url = URL::getService( RouterInterface::class )->getContext()->getPathInfo();

        if ( $append ) {
            $url .= '/' . trim( $append, '/' );
        }

        return $url;
    }

    public static function normalize( string $url, ?bool $trailingSlash = null ) : mixed {

        $trailingSlash ??= Settings::get( 'url.trailingSlash' );

        $explode = \Northrook\Support\Arr::explode( '/', $url );

        $url = '/' . implode( '/', $explode );

        return $trailingSlash ? $url . '/' : $url;
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
                Log::log(
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