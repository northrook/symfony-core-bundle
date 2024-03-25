<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Services;

use Northrook\Stylesheets\ColorPalette;
use Northrook\Stylesheets\DynamicRules;
use Northrook\Stylesheets\Stylesheet;
use Northrook\Types\Path;

class StylesheetGenerationService
{
    // @todo Move this to config, as primary only.
    // When just provided a primary color, autogenerate a light and dark baseline color
    private const PALETTE = [
        'baseline' => '222,9,10',
        'primary'  => '222,100,50',
    ];

    private readonly string $rootDirectory;
    private readonly Path   $savePath;

    /** @var Path[] */
    private array $templateDirectories = [];
    private array $includedStylesheets = [];


    protected readonly Stylesheet $generator;
    public readonly string        $stylesheet;
    public ?ColorPalette          $palette = null;
    public bool                   $force   = false;


    public function __construct(
        private readonly PathfinderService     $pathfinder,
        private readonly CurrentRequestService $request,
        private readonly ?LoggerInterface      $logger = null,
        private readonly ?Stopwatch            $stopwatch = null,
    ) {
        $this->rootDirectory = $this->pathfinder->get( 'dir.root' )->value;

        $this->palette = new ColorPalette( self::PALETTE );
    }

    public function includeStylesheets( string | array $path ) : self {
        $this->includedStylesheets = array_merge( $this->includedStylesheets, (array) $path );
        return $this;
    }

    public function scanTemplateFiles( string ...$in ) : self {
        foreach ( $in as $path ) {
            $this->templateDirectories[] = new Path( $path );
        }
        return $this;
    }


    /**
     * @param Path|string  $path
     *
     * @return bool True when saved, false when not
     */
    public function save( Path | string $path ) : bool {
        $this->savePath = $path instanceof Path ? $path : $this->pathfinder->get( $path );

        $this->palette ??= new ColorPalette( self::PALETTE );

        $this->generator = new Stylesheet(
            $this->palette,
            $this->force
        );

        foreach ( $this->includedStylesheets as $stylesheet ) {
            if ( substr_count( $stylesheet, '.' ) > 1 ) {
                $path = new Path( strchr( $stylesheet, '.', true ) . '.css' );
                if ( $path->isValid ) {
                    $this->generator->addStylesheets( (string) $path );
                }
            }
            $stylesheet = new Path( $stylesheet );
            if ( $stylesheet->isValid ) {
                $this->generator->addStylesheets( (string) $stylesheet );
            }
        }

        if ( empty( $this->templateDirectories ) ) {
            $this->templateDirectories[] = $this->rootDirectory . 'templates';
        }

        $this->generator->dynamicRules = new DynamicRules(
            $this->rootDirectory,
            $this->templateDirectories
        );

        $this->generator->build();

        $this->stylesheet = (string) $this->generator;

        dd( $this );

        return true;
    }
}