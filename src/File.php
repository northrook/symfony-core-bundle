<?php

namespace Northrook\Symfony\Core;

use JetBrains\PhpStorm\Deprecated;use Northrook\Support\Functions\FilesystemFunctions;use Northrook\Symfony\Core\Services\PathfinderService;use Northrook\Types as Type;

#[Deprecated]
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


}