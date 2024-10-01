<?php

declare(strict_types=1);

namespace Northrook\Symfony\Core\Response\ResponseHandler;

use JetBrains\PhpStorm\Deprecated;
use Northrook\Assets\Script;
use Northrook\{Clerk, Minify};
use Northrook\Logger\Log;
use Northrook\Resource\Path;
use function Northrook\regexNamedGroups;
use const Northrook\EMPTY_STRING;

// order : dynamic | manual
// in relation to default loading core and lazy loading all ui assets,
// but setting should allow for preloading manually, and for manually
// inserting an asset at a given order

#[Deprecated]
final class AssetHandler
{
    public bool $minify = true;

    public bool $removeTaggedSubstrings = true;

    public function htmx(
        string $source,
        string $assetID = 'htmx-core',
    ) : Script {
        Clerk::event( $this::class."::{$assetID}", 'document' );
        $htmx = $this->parseJavaScript( new Path( $source ), 'htmx-core' );
        Clerk::stop( $this::class."::{$assetID}" );
        return $htmx;
    }

    private function parseJavaScript( string|Path $source, string $assetID ) : Script
    {
        $string = $source instanceof Path ? $source->read : $source;

        if ( $source instanceof Path && \str_ends_with( $source->path, 'min.js' ) ) {
            return new Script( $string, $assetID );
        }

        if ( $this->removeTaggedSubstrings ) {
            foreach ( regexNamedGroups(
                '#(?<start>\/\/!!::)(?<comment>\h*.*?\v)(?<remove>.+?)(?<end>\/\/::!!)#s',
                $string,
            ) as $removeTag ) {
                Clerk::event( $this::class."::{$assetID}" );
                $comment = \trim( $removeTag['comment'] );
                Log::info(
                    'Removed tagged substring from {assetID}'.( $comment ? ', {comment}.' : '.' ),
                    [
                        'assetID' => $assetID,
                        'comment' => $comment,
                        'removed' => $removeTag['remove'],
                    ],
                );

                $string = \str_replace( $removeTag['match'], EMPTY_STRING, $string );
            }
        }

        if ( $this->minify ) {
            Clerk::event( $this::class."::{$assetID}" );
            $string = Minify::JS( $string, $assetID );
        }

        Clerk::event( $this::class."::{$assetID}" );
        return new Script( $string, $assetID );
    }
}
