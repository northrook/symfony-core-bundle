<?php

namespace Northrook\Symfony\Core\Support;

use JetBrains\PhpStorm\ExpectedValues;
use Northrook\Logger\Log;
use Northrook\Types as Type;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

final class Str
{
    public static function contains(
        string         $string,
        string | array $needle,
        bool           $returnNeedles = false,
        bool           $containsOnlyOne = false,
    ) : int | array {

        $count    = 0;
        $contains = [];
        $search   = strtolower( $string );

        if ( is_string( $needle ) ) {
            $count = substr_count( $search, strtolower( $needle ) );
        }
        else {
            foreach ( $needle as $value ) {
                $match = substr_count( $search, strtolower( $value ) );
                if ( $match ) {
                    $contains[] = $value;
                    $count      += $match;
                }
            }
        }

        if ( $containsOnlyOne && $count !== 1 ) {
            return 0;
        }

        if ( $returnNeedles ) {
            return $contains;
        }

        return $count;
    }

    public static function parameterDirname(
        string  $path = '%kernel.project_dir%',
        #[ExpectedValues( [ 'log', 'error', 'exception' ] )]
        ?string $onInvalidPath = 'exception',
    ) : ?string {

        if ( false === str_starts_with( $path, '../' ) ) {
            return Type\Path::normalize( $path );
        }

        $level = substr_count( $path, '../', 0, strripos( $path, '../' ) + 3 );
        $root  = dirname( debug_backtrace()[ 0 ][ 'file' ], $level ?: 1 );
        $path  = $root . '/' . substr( $path, strripos( $path, '../' ) + 3 );

        $path = Type\Path::normalize( $path );

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