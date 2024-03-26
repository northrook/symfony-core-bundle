<?php

namespace Northrook\Symfony\Core;

use JetBrains\PhpStorm\ExpectedValues;
use Northrook\Logger\Log;
use Northrook\Symfony\Core\Services\PathfinderService;
use Northrook\Symfony\Core\Support\Str;
use Northrook\Types\Path;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
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


    /**
     * Atomically dumps content into a file.
     *
     * @param string|resource  $content  The data to write into the file
     *
     * @throws IOException if the file cannot be written to
     */
    public static function save( string $path, mixed $content ) : bool {
        try {
            ( new Filesystem() )->dumpFile( $path, $content );
            return true;
        }
        catch ( IOException $e ) {
            Log::Error( message : $e->getMessage(), context : [ 'exception' => $e ] );
        }

        return false;
    }


    /**
     * Copies a file.
     *
     * If the target file is older than the origin file, it's always overwritten.
     * If the target file is newer, it is overwritten only when the
     * $overwriteNewerFiles option is set to true.
     *
     */
    public static function copy( string $originFile, string $targetFile, bool $overwriteNewerFiles = false ) : bool {
        try {
            ( new Filesystem() )->copy( $originFile, $targetFile, $overwriteNewerFiles );
            return true;
        }
        catch ( FileNotFoundException | IOException $e ) {
            Log::Error( message : $e->getMessage(), context : [ 'exception' => $e ] );
        }

        return false;
    }


    /**
     * Creates a directory recursively.
     */
    public static function mkdir( string | iterable $dirs, int $mode = 0777 ) : bool {
        try {
            ( new Filesystem() )->mkdir( $dirs, $mode );
            return true;
        }
        catch ( IOException $e ) {
            Log::Error( message : $e->getMessage(), context : [ 'exception' => $e ] );
        }
        return false;
    }


    /**
     * Checks the existence of files or directories.
     */
    public static function exists( string | iterable $files ) : bool {
        return ( new Filesystem() )->exists( $files );
    }

    /**
     * Sets access and modification time of file.
     *
     * @param int|null  $time   The touch time as a Unix timestamp, if not supplied the current system time is used
     * @param int|null  $atime  The access time as a Unix timestamp, if not supplied the current system time is used
     *
     */
    public static function touch( string | iterable $files, ?int $time = null, ?int $atime = null ) : bool {
        try {
            ( new Filesystem() )->touch( $files, $time, $atime );
            return true;
        }
        catch ( IOException $e ) {
            Log::Error( message : $e->getMessage(), context : [ 'exception' => $e ] );
        }
        return false;
    }

    /**
     * Removes files or directories.
     */
    public static function remove( string | iterable $files ) : bool {
        try {
            ( new Filesystem() )->remove( $files );
            return true;
        }
        catch ( IOException $e ) {
            Log::Error( message : $e->getMessage(), context : [ 'exception' => $e ] );
        }
        return false;
    }

    /**
     * Renames a file or a directory.
     */
    public static function rename( string $origin, string $target, bool $overwrite = false ) : bool {
        try {
            ( new Filesystem() )->rename( $origin, $target, $overwrite );
            return true;
        }
        catch ( IOException $e ) {
            Log::Error( message : $e->getMessage(), context : [ 'exception' => $e ] );
        }
        return false;
    }
}