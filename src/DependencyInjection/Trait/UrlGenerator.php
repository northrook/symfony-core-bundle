<?php

namespace Northrook\Symfony\Core\DependencyInjection\Trait;

use JetBrains\PhpStorm\Deprecated;
use Northrook\Symfony\Core\DependencyInjection\CoreDependencies;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Deprecated]
trait UrlGenerator
{
    protected readonly CoreDependencies $get;

    /**
     * Generates a URL from the given parameters.
     *
     * @see UrlGeneratorInterface
     */
    final protected function generateUrl(
        string $route,
        array  $parameters = [],
        int    $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
    ) : string {
        return $this->get->router->generate( $route, $parameters, $referenceType );
    }
}