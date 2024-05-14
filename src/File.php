<?php

namespace Northrook\Symfony\Core;

use Northrook\Support\Functions\FilesystemFunctions;use Northrook\Symfony\Core\Services\PathfinderService;use Northrook\Types as Type;

final class File extends SymfonyCoreFacade
{

    use FilesystemFunctions;

    /**
     * @param string  $get  {@see ParameterBagInterface::get}
     *
     * @return Path
     */
    public static function path( string $get ) : Type\Path {
        return new Type\Path(Path::get( $get ));
    }

    public static function pathfinder() : PathfinderService {
        return File::getPathfinderService();
    }

}