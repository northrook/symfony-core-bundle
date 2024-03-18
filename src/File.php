<?php

namespace Northrook\Symfony\Core;

use Northrook\Logger\Log;
use Northrook\Symfony\Core\Support\Str;
use Northrook\Types\Path;

final class File extends SymfonyCoreFacade
{

    private static array $cache = [];

    /**
     * @param string  $get  {@see ParameterBagInterface::get}
     *
     * @return Path
     */
    public static function path( string $get ) : Path {
        return self::getPathfinderService()->get( $get );
    }

    public static function getMimeType( Path | string $path ) : ?string {
        $types = File::$cache[ 'mime.types' ] ??= include( Str::parameterDirname( '../resources/mimetypes.php' ) );

        if ( array_key_exists( $path->extension, $types ) ) {
            return $types[ $path->extension ];
        }
        
        return null;
    }

    public static function getContent( Path | string $path, bool $cache = true ) : ?string {

        if ( is_string( $path ) ) {
            $path = new Path( $path );
        }

        if ( $cache && isset( self::$cache[ $path->value ] ) ) {
            return self::$cache[ $path->value ];
        }

        if ( !$path->isValid ) {
            Log::Error(
                'The file {key} was parsed, but {error}. No file was found.',
                [
                    'key'   => $path->value,
                    'error' => 'does not exist',
                    'path'  => $path,
                ],
            );
            return null;
        }

        $content = file_get_contents( $path );

        if ( $path->extension === 'svg' ) {
            $content = str_replace(
                [ ' xmlns="http://www.w3.org/2000/svg"', ' xmlns:xlink="http://www.w3.org/1999/xlink"' ],
                '',
                $content,
            );
        }

        if ( $cache ) {
            self::$cache[ $path->value ] = $content;
        }

        return $content;
    }
}