<?php

namespace Northrook\Symfony\Core;

use Northrook\Types\Path;

final class File extends SymfonyCoreFacade
{
    /**
     * @param string  $get  {@see ParameterBagInterface::get}
     *
     * @return Path
     */
    public static function path( string $get ) : Path {
        return self::pathfinderService()->get( $get );
    }
}