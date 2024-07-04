<?php

namespace Northrook\Symfony\Core\Facade;

use Northrook\Symfony\Core\Autowire\Pathfinder;
use Northrook\Symfony\Core\DependencyInjection\Facade;
use Northrook\Symfony\Core\DependencyInjection\StaticFacade;

/**
 * @method static string get( string $path )
 */
final class Path extends Facade
{
    use StaticFacade;

    protected const SERVICE = Pathfinder::class;

    public static function getDirectories() : array {
        return Path::getService()->directories;
    }
}