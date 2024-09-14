<?php

declare ( strict_types = 1 );

namespace Northrook\Symfony\Core\Service;

use Northrook\CSS\Stylesheet;
use Northrook\Get;
use Northrook\Trait\PropertyAccessor;
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
     * @param DesignSystemService  $designSystem
     * @param ?LoggerInterface     $logger
     */
    public function __construct(
        private readonly DesignSystemService $designSystem,
        private readonly ?LoggerInterface    $logger,
    ) {}

    public function __get( string $property )
    {
        return match ( $property ) {
            'admin'  => $this->adminStylesheet(),
            'public' => $this->publicStylesheet(),
        };
    }

    public function stylesheet(
        string $savePath,
        array  $sourceDirectories = [],
        array  $templateDirectories = [],
    ) : Stylesheet
    {
        $path = Get::path( $savePath );
        return $this->stylesheets[ $path ] ??= new Stylesheet(
            $path,
            $sourceDirectories,
            $templateDirectories,
        );
    }

    private function globalStyles() : array
    {
        return [
            Get::path( 'dir.ui.assets/styles' ),
            Get::path( 'dir.core.assets/styles' ),
        ];
    }

    private function adminStylesheet() : Stylesheet
    {
        if ( isset( $this->stylesheets[ 'admin' ] ) ) {
            return $this->stylesheets[ 'admin' ];
        }

        $admin = new Stylesheet(
            Get::path( 'path.admin.stylesheet' ),
            [
                $this->designSystem->admin()->colorPalette->generateStyles(),
                ...$this->globalStyles(),
                Get::path( 'dir.assets/admin/styles' ),
                Get::path( 'dir.core.assets/admin/styles' ),
            ],
            [], // templates
            $this->logger,
        );

        $admin
            ->addReset()
            ->addBaseline()
            ->addDynamicRules()
        ;

        return $this->stylesheets[ 'admin' ] = $admin;
    }

    private function publicStylesheet() : Stylesheet
    {
        if ( isset( $this->stylesheets[ 'public' ] ) ) {
            return $this->stylesheets[ 'public' ];
        }

        $public = new Stylesheet(
            Get::path( 'path.public.stylesheet' ),
            [
                $this->designSystem->admin()->colorPalette->generateStyles(),
                ...$this->globalStyles(),
                Get::path( 'dir.assets/public/styles' ),
            ],
            [], // templates
            $this->logger,
        );

        $public
            ->addReset()
            ->addBaseline()
            ->addDynamicRules()
        ;

        return $this->stylesheets[ 'public' ] = $public;
    }

}