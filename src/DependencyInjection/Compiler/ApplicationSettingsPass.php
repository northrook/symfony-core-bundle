<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\DependencyInjection\Compiler;

use Northrook\Settings;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use function Northrook\normalizePath;

final readonly class ApplicationSettingsPass implements CompilerPassInterface
{
    public function __construct( private string $projectDir ) {}

    public function process( ContainerBuilder $container ) : void {

        // Assign the path parameters to the Pathfinder service
        $container->getDefinition( Settings::class )
                  ->replaceArgument( 0, $this->getCoreSettings( $container->getParameterBag() ) );
    }

    private function getCoreSettings( ParameterBagInterface $parameters ) : array {

        $dirRoot  = $parameters->get( 'kernel.project_dir' );
        $dirCache = $parameters->get( 'kernel.cache_dir' );

        $settings = [
            'app.env'   => $parameters->get( 'kernel.environment' ),
            'app.debug' => $parameters->get( 'kernel.debug' ),
            'app.mode'  => $parameters->get( 'kernel.runtime_mode' ),
        ];

        $paths = [
            'dir.root'           => $dirRoot,
            'dir.src'            => $dirRoot . '/src',
            'dir.public'         => $dirRoot . '/public',
            'dir.public.assets'  => $dirRoot . '/public/assets',
            'dir.var'            => $dirRoot . '/var',
            'dir.cache'          => $dirCache,
            'dir.cache.latte'    => $dirCache . '/latte',
            'dir.storage'        => $dirRoot . '/storage',
            'dir.build'          => $parameters->get( 'kernel.build_dir' ),
            'dir.logs'           => $parameters->get( 'kernel.logs_dir' ),
            'dir.manifest'       => $dirRoot . '/var/manifest',
            'dir.config'         => $dirRoot . '/config',
            'dir.assets'         => $dirRoot . '/assets',
            'dir.templates'      => $dirRoot . '/templates',
            'dir.core.assets'    => \dirname( __DIR__, 2 ) . '/assets',
            'dir.core.templates' => \dirname( __DIR__, 2 ) . '/templates',
        ];

        foreach ( $paths as $name => $path ) {
            $settings[ $name ] = normalizePath( $path );
        }

        $settings += [
            'site.public'            => false,
            'site.name'              => 'Symfony',
            'site.url'               => $parameters->get( 'kernel.url' ),
            'site.locale'            => $parameters->get( 'kernel.default_locale' ),
            'site.locales_available' => $parameters->get( 'kernel.enabled_locales' ),
            'site.charset'           => \strtolower( $parameters->get( 'kernel.charset' ) ),
        ];

        $settings += [
            'admin.url' => $parameters->get( 'kernel.url' ) . '/admin',
        ];

        $settings += [
            'mailer.dsn'  => $_ENV[ 'HOME_URL' ] ?? null,
            'mailer.from' => $_ENV[ 'MAILER_DSN' ] ?? null,
            'mailer.name' => $_ENV[ 'MAILER_FROM' ] ?? null,
            'mailer.lang' => $_ENV[ 'MAILER_NAME' ] ?? null,
        ];
        echo \count( $settings ) . ' generated';

        return $settings;
    }

    /**
     *  Get path parameters from the {@see ParameterBag}
     *
     * - Parses through all `string` parameters
     * - Only keys containing `dir` or `path` will be considered
     * - Only values starting with the {@see projectDir} are used
     *
     * @param array  $parameters
     *
     * @return array
     */
    private function getPathEntries( array $parameters ) : array {


        $paths = \array_filter(
            array    : $parameters,
            callback : fn ( $value, $key ) => \is_string( $value ) &&
                                              ( \str_starts_with( $key, 'dir' ) ||
                                                \str_starts_with( $key, 'path' ) ) &&
                                              \str_starts_with( $value, $this->projectDir ),
            mode     : ARRAY_FILTER_USE_BOTH,
        );

        // Sort and normalise
        foreach ( $paths as $key => $value ) {

            // Simple sorting; unsetting 'dir' and 'path' prefixed keys, appending them after all Symfony-defined directories
            if ( \str_starts_with( $key, 'dir' ) || \str_starts_with( $key, 'path' ) ) {
                unset( $paths[ $key ] );
            }

            // Normalise each path
            $paths[ $key ] = normalizePath( $value );
        }

        return $paths;
    }
}