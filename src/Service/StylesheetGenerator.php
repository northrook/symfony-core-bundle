<?php

declare ( strict_types = 1 );

namespace Northrook\Symfony\Core\Service;

use Northrook\Core\Trait\PropertyAccessor;
use Northrook\CSS\Stylesheet;
use Northrook\Symfony\Core\Autowire\Pathfinder;
use Psr\Log\LoggerInterface;

/**
 * @property-read Stylesheet $admin
 * @property-read Stylesheet $public
 */
final class StylesheetGenerator
{
    use PropertyAccessor;

    /**
     * @var array{string: Stylesheet}
     */
    private array $stylesheets = [];

    /**
     * @param Pathfinder        $pathfinder
     * @param ?LoggerInterface  $logger
     */
    public function __construct(
        private readonly Pathfinder          $pathfinder,
        private readonly DesignSystemService $designSystem,
        private readonly ?LoggerInterface    $logger,
    ) {}

    public function __get( string $property ) {
        return match ( $property ) {
            'admin'  => $this->adminStylesheet(),
            'public' => $this->publicStylesheet(),
        };
    }

    public function stylesheet(
        string $savePath,
        array  $sourceDirectories = [],
        array  $templateDirectories = [],
    ) : Stylesheet {
        $path = $this->pathfinder->get( $savePath );
        return $this->stylesheets[ $path ] ??= new Stylesheet(
            $path,
            $sourceDirectories,
            $templateDirectories,
        );
    }

    private function adminStylesheet() : Stylesheet {

        if ( isset( $this->stylesheets[ 'admin' ] ) ) {
            return $this->stylesheets[ 'admin' ];
        }

        $admin = new Stylesheet(
            $this->pathfinder->get( 'dir.var.stylesheet' ),
            [
                $this->designSystem->admin()->colorPalette->generateStyles(),
                $this->pathfinder->get( 'dir.core.assets/admin/styles' ),
                $this->pathfinder->get( 'dir.assets/admin/styles' ),
            ],
            [], // templates
            $this->logger,
        );

        $admin->addReset()
              ->addBaseline()
              ->addDynamicRules();

        return $this->stylesheets[ 'admin' ] = $admin;
    }

    private function publicStylesheet() : Stylesheet {

        if ( isset( $this->stylesheets[ 'public' ] ) ) {
            return $this->stylesheets[ 'public' ];
        }

        $public = new Stylesheet(
            $this->pathfinder->get( 'path.public.stylesheet' ),
            [ $this->pathfinder->get( 'dir.assets/public/styles' ) ],
            [], // templates
            $this->logger,
        );

        return $this->stylesheets[ 'public' ] = $public;
    }

}