<?php

namespace Northrook\Symfony\Core\DependencyInjection\Compiler;

use Northrook\AssetManager;
use Northrook\Symfony\Service\Document\DocumentService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final readonly class AssetManagerPass implements CompilerPassInterface
{

    public function __construct() {}

    public function process( ContainerBuilder $container ) : void {
        // Assign the path parameters to the Pathfinder service
        $container->getDefinition( DocumentService::class )
                  ->replaceArgument(
                      0,
                      $container->getDefinition( AssetManager::class ),
                  );
    }
}