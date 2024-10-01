<?php

declare(strict_types=1);

namespace Northrook\Symfony\Core\Facade;

use Northrook\Logger\Log;
use Northrook\Symfony\Core\DependencyInjection\ServiceContainer;
use Support\Normalize;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use function Assert\isUrl;

/**
 * @see UrlGeneratorInterface, RouterInterface
 */
final class URL
{
    public static function router() : RouterInterface
    {
        return ServiceContainer::get( RouterInterface::class );
    }

    /**
     * Get the URL or path for a specific route based on the given parameters.
     *
     * Uses {@see UrlGeneratorInterface::generate()} to generate the URL.
     *
     * @param string $route
     * @param array  $parameters
     * @param int    $referenceType
     *
     * @return string
     */
    public static function get(
        string $route,
        array  $parameters = [],
        int    $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
    ) : string {
        return URL::router()->generate( $route, $parameters, $referenceType );
    }

    /**
     * Returns the base URL.
     *
     * @param null|string $append
     * @param bool        $absolute
     *
     * @return string
     *
     * @todo This needs to retrieve the full URL, not just the base path by default.
     */
    public static function base( ?string $append = null, bool $absolute = false ) : string
    {
        $url = URL::router()->getContext()->getBaseUrl();

        if ( $append ) {
            $url .= '/'.\trim( $append, '/' );
        }

        return $url;
    }

    /**
     * Returns the current URL.
     *
     * @param null|string $append
     *
     * @return string
     */
    public static function current( ?string $append = null ) : string
    {
        $url = URL::router()->getContext()->getPathInfo();

        if ( $append ) {
            $url .= '/'.\trim( $append, '/' );
        }

        return $url;
    }

    public static function normalize( string $url, ?bool $trailingSlash = null ) : string
    {
        return Normalize::path( $url, $trailingSlash );
    }

    /**
     * Determine if the given path is a valid URL.
     *
     * @param string  $url
     * @param ?string $requiredProtocol
     * @param bool    $logInsecureUrl   whether to log insecure URLs
     *
     * @return bool
     */
    public static function isValid(
        string  $url,
        ?string $requiredProtocol = null,
        bool    $logInsecureUrl = true,
    ) : bool {
        if ( ! isUrl( $url, $requiredProtocol ) ) {
            return false;
        }

        if ( $logInsecureUrl && \str_starts_with( $url, 'http://' ) ) {
            Log::entry(
                'https' === $requiredProtocol ? 'error' : 'warning',
                'The URL {url} is not secure. Please use https:// instead.',
                ['url' => $url],
            );
        }

        return true;
    }
}
