<?php

// --------------------------------------------------------------------
// Latte Environment Pass
// --------------------------------------------------------------------

declare(strict_types=1);

namespace Northrook\Symfony\Core\DependencyInjection\CompilerPass;

use Northrook\Assets\AssetManager\AssetCompiler;
use Northrook\Logger\Log;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class AssetCompilerPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container ) : void
    {
        $cacheAdapter = $container->getDefinition( 'cache.assets' );

        if ( ! $cacheAdapter instanceof CacheInterface ) {
            Log::critical( 'Cache cache adapter is not configured' );
            return;
        }

        $assetCompiler = new AssetCompiler( $cacheAdapter );

        foreach ( $this->getAssetBundles( $container->getParameterBag() ) as $label => $source ) {
            $assetCompiler->register( $label, ...(array) $source );
        }
    }

    /**
     * @param ParameterBagInterface $parameterBag
     *
     * @return array<string, array<int,string>|string>
     */
    private function getAssetBundles( ParameterBagInterface $parameterBag ) : array
    {
        return \array_filter(
            array    : $parameterBag->all(),
            callback : fn( $value, $key ) => ( \is_string( $value ) || \is_array( $value ) )
                                             && \str_starts_with( $key, 'asset' ),
            mode     : ARRAY_FILTER_USE_BOTH,
        );
    }
}
