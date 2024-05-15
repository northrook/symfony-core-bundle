<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Services;

use Northrook\Core\Service\Status;
use Northrook\Core\Type\PathType;
use Northrook\Stylesheets\ColorPalette;
use Northrook\Stylesheets\Stylesheet;
use Northrook\Support\Arr;
use Northrook\Support\File;
use Northrook\Symfony\Core\Path;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * TODO : Create a Palette cache file. Readable from {@see Settings::site()->palette}
 */
final class StylesheetGenerationService
{
    // @todo Move this to config, as primary only.
    // When just provided a primary color, autogenerate a light and dark baseline color
    private const PALETTE = [
        'baseline' => '222,9,10',
        'primary'  => '222,100,50',
    ];

    private readonly string $rootDirectory;

    private array $templateDirectories = [];
    private array $includedStylesheets = [];

    protected readonly Stylesheet $generator;
    public readonly string        $stylesheet;
    public ?ColorPalette          $palette = null;
    public bool                   $force   = false;
    public readonly bool          $updated;


    public function __construct(
        private readonly CurrentRequestService $session,
        private readonly PathfinderService     $pathfinder,
        private readonly ?LoggerInterface      $logger = null,
        private readonly ?Stopwatch            $stopwatch = null,
    ) {
        $this->rootDirectory = \Northrook\Symfony\Core\Path::get( 'dir.root' );

        $this->palette = new ColorPalette( StylesheetGenerationService::PALETTE );
    }

    public function includeStylesheets( string | array $path ) : self {

        foreach ( (array) $path as $add ) {

            /** @var PathType $stylesheet */
            $stylesheet = ( $add instanceof PathType ) ? $ $add : new Stylesheet( $add );

            if ( ! $stylesheet->exists ) {
                $this->logger?->error(
                    'Stylesheet {stylesheet} does not exist. File not found. Skipping.',
                    [
                        'stylesheet' => $stylesheet->value,
                        'service'    => $this::class,
                    ],
                );
                continue;
            }

            $this->includedStylesheets[] = (string) $add;
        }

        return $this;
    }

    public function scanTemplateFiles( string ...$in ) : self {
        foreach ( $in as $path ) {
            $this->templateDirectories[] = $this->pathfinder->get( $path );
        }
        return $this;
    }

    public function generate(
        array $includeStylesheets = [ 'dir.core.assets/styles' ],
    ) : bool {

        $this->includeStylesheets( $includeStylesheets );
        $this->force = true;

        return $this->save()->success;
    }


    /**
     * @param null|Path|string  $path   Defaults to `var/cache/styles/styles.css`
     * @param null|bool         $force  Force updating the stylesheet, even if no monitored .css files have changed
     *
     * @return Status
     */
    public function save( null | PathType | string $path = null, ?bool $force = null ) : Status {

        $force ??= $this->force;

        $this->stopwatch->start( 'save', 'StylesheetGenerationService' );

        if ( $path ) {
            $path = $path instanceof PathType ? $path : $this->pathfinder->get( $path );
        }
        else {
            $path = $this->pathfinder->get( 'dir.cache/styles/styles.css' );
        }

        $templates = array_filter(
            $this->pathfinder->getParameters(),
            static fn ( $v, $key ) => str_contains( $key, 'templates' ),
            ARRAY_FILTER_USE_BOTH,
        );

        $this->templateDirectories = Arr::unique( $templates + $this->templateDirectories );

        $this->generator = new Stylesheet(
            $this->rootDirectory,
            $this->palette,
            $this->templateDirectories,
        );

        foreach ( $this->includedStylesheets as $stylesheet ) {
            if ( substr_count( $stylesheet, '.' ) > 1 ) {
                $path = new PathType( strstr( $stylesheet, '.', true ) . '.css' );
                if ( $path->exists ) {
                    $this->generator->addStylesheets( (string) $path );
                }
            }
            $stylesheet = new PathType( $stylesheet );
            if ( $stylesheet->exists ) {
                $this->generator->addStylesheets( (string) $stylesheet );
            }
        }

        $status = new Status();

        $this->generator->force = $force;
        $this->generator->build();

        $this->stylesheet = $this->generator->styles ?? '';

        if ( !$this->stylesheet ) {
            $status->set( 'notice' );
            $this->logger?->error(
                'Stylesheet was empty',
                [ 'service' => $this ],
            );
            return $status;
        }

        $status->set( 'success' );

        $this->updated = File::save( $path->value, $this->stylesheet );

        $this->stopwatch->stop( 'save' );


        $this->session->addFlash(
            $status->status,
            'Stylesheet regenerated.',
        );

        return $status;
    }
}