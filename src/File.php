<?php

namespace Northrook\Symfony\Core;

use JetBrains\PhpStorm\ExpectedValues;
use Northrook\Logger\Log;
use Northrook\Symfony\Core\Services\PathfinderService;
use Northrook\Symfony\Core\Support\Str;
use Northrook\Types\Path;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

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

    public static function pathfinder() : PathfinderService {
        return self::getPathfinderService();
    }


    public static function getMimeType( Path | string $path ) : ?string {
        $types = File::$cache[ 'mime.types' ] ??= include( Str::parameterDirname( '../../resources/mimetypes.php' ) );

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

    public static function parameterDirname(
        string  $path = '%kernel.project_dir%',
        #[ExpectedValues( [ 'log', 'error', 'exception' ] )]
        ?string $onInvalidPath = 'exception',
    ) : ?string {

        if ( false === str_starts_with( $path, '../' ) ) {
            return Path::normalize( $path );
        }

        $level = substr_count( $path, '../', 0, strripos( $path, '../' ) + 3 );
        $root  = dirname( debug_backtrace()[ 0 ][ 'file' ], $level ?: 1 );
        $path  = $root . '/' . substr( $path, strripos( $path, '../' ) + 3 );

        $path = Path::normalize( $path );

        if ( file_exists( $path ) ) {
            return $path;
        }

        match ( $onInvalidPath ) {
            'exception' => throw new FileNotFoundException( $path ),
            'error'     => trigger_error( "File \"$path\" does not exist.", E_USER_ERROR ),
            'log'       => Log::Error(
                message : 'File {path} does not exist.',
                context : [ 'path' => $path, 'file' => debug_backtrace()[ 0 ][ 'file' ] ],
            ),
            default     => null,
        };

        return $path;
    }
}