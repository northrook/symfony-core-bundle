<?php

namespace Northrook\Symfony\Core\Facade;

use Northrook\Symfony\Core\DependencyInjection\Facade;
use Symfony\Component\Routing\RouterInterface;

final class URL extends Facade
{

    public static function generate( string $path ) : string {
        return URL::getService( RouterInterface::class )->generate( $path );
    }

}