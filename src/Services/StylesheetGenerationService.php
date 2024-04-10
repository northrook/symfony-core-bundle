<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Services;

use Northrook\Stylesheets\ColorPalette;
use Northrook\Stylesheets\Stylesheet;
use Northrook\Symfony\Core\File;
use Northrook\Types\Path;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * TODO : Create a Palette cache file. Readable from {@see Settings::site()->palette}
 */
class StylesheetGenerationService
{
    // @todo Move this to config, as primary only.
    // When just provided a primary color, autogenerate a light and dark baseline color
    private const PALETTE = [
        'baseline' => '222,9,10',
        'primary'  => '222,100,50',
    ];

    private readonly string $rootDirectory;

    /** @var Path[] */
    private array $templateDirectories = [];
    private array $includedStylesheets = [];


    protected readonly Stylesheet $generator;
    public readonly string        $stylesheet;
    public ?ColorPalette          $palette = null;
    public bool                   $force   = false;
    public readonly bool          $updated;


    public function __construct(
        private readonly PathfinderService $pathfinder,
        private readonly ?LoggerInterface  $logger = null,
        private readonly ?Stopwatch        $stopwatch = null,
    ) {
        $this->rootDirectory = $this->pathfinder->get( 'dir.root' )->value;

        $this->palette = new ColorPalette( StylesheetGenerationService::PALETTE );
    }

    public function includeStylesheets( string | array $path ) : self {

        foreach ( (array) $path as $add ) {

            if ( $add instanceof Path ) {
                $this->includedStylesheets[] = (string) $add;
            }
            else {
                $add = File::path( $add );

                if ( $add->exists ) {
                    $this->includedStylesheets[] = (string) $add;
                }
            }
        }

        return $this;
    }

    public function scanTemplateFiles( string ...$in ) : self {
        foreach ( $in as $path ) {
            $this->templateDirectories[] = new Path( $path );
        }
        return $this;
    }


    /**
     * @param null|Path|string  $path  Defaults to var/cache/assets/styles.css
     * @param null|bool         $force
     *
     * @return bool True when saved, false when not
     */
    public function save( null | Path | string $path = null, ?bool $force = null ) : bool {

        $force ??= $this->force;

        $this->stopwatch->start( 'save', 'StylesheetGenerationService' );

        if ( $path ) {
            $path = $path instanceof Path ? $path : $this->pathfinder->get( $path );
        }
        else {
            $path = $this->pathfinder->get( 'dir.cache/assets/styles.css' );
        }

        $this->templateDirectories = [
                                         $this->pathfinder->get( 'dir.templates' ),
                                         $this->pathfinder->get( 'dir.core.templates' ),
                                     ] + $this->templateDirectories;

        // dd(
        //     $this->pathfinder::getCache(),
        // );

        $this->generator = new Stylesheet(
            $this->rootDirectory,
            $this->palette,
            $this->templateDirectories,
        );

        foreach ( $this->includedStylesheets as $stylesheet ) {
            if ( substr_count( $stylesheet, '.' ) > 1 ) {
                $path = new Path( strstr( $stylesheet, '.', true ) . '.css' );
                if ( $path->isValid ) {
                    $this->generator->addStylesheets( (string) $path );
                }
            }
            $stylesheet = new Path( $stylesheet );
            if ( $stylesheet->isValid ) {
                $this->generator->addStylesheets( (string) $stylesheet );
            }
        }

        $this->generator->force = $force;
        $this->generator->build();

        $this->stylesheet = $this->generator->styles;

        if ( !$this->stylesheet ) {
            $this->logger?->error(
                'Stylesheet was empty',
                [ 'service' => $this ],
            );
            return false;
        }

        $this->updated = File::save( $path->value, $this->stylesheet );

        $this->stopwatch->stop( 'save' );

        return $this->updated;
    }
}