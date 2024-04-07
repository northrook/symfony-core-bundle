<?php

namespace Northrook\Symfony\Core;

use Northrook\Support\Functions\FilesystemFunctions;
use Northrook\Symfony\Core\Services\PathfinderService;
use Northrook\Types\Path;

final class File extends SymfonyCoreFacade
{

    use FilesystemFunctions;

    /**
     * @param string  $get  {@see ParameterBagInterface::get}
     *
     * @return Path
     */
    public static function path( string $get ) : Path {
        return File::getPathfinderService()->get( $get );
    }

    public static function pathfinder() : PathfinderService {
        return File::getPathfinderService();
    }

}