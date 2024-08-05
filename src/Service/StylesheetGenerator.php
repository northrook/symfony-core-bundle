<?php

declare ( strict_types = 1 );

namespace Northrook\Symfony\Core\Service;

use Northrook\CSS\Stylesheet;
use Northrook\Symfony\Core\Autowire\Pathfinder;
use Psr\Log\LoggerInterface;

final readonly class StylesheetGenerator
{
    private Stylesheet $adminStyles;

    /**
     * @param Pathfinder        $pathfinder
     * @param ?LoggerInterface  $logger
     */
    public function __construct(
        private Pathfinder       $pathfinder,
        private ?LoggerInterface $logger,
    ) {}

    public function adminStyles() : Stylesheet {
        $this->adminStyles ??= new Stylesheet(
            $this->pathfinder->get( 'path.admin.stylesheet' ),
            [ $this->pathfinder->get( 'dir.assets/admin' ) ],
            [], // templates
            $this->logger,
        );

        return $this->adminStyles;
    }

}