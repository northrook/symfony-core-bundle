<?php

namespace Northrook\Symfony\Core\DependencyInjection;

use Northrook\Logger\Log;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel as App;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @internal
 *
 * @author Martin Nielsen <mn@northrook.com>
 *
 */
abstract class ContainerFacade
{
//	protected const KERNEL_DIR = [
//		'root',          // ~symfony/
//		'assets',        // ~symfony/assets/
//		'public',        // ~symfony/public/
//		'public.assets', // ~symfony/public/assets/
//		'templates',     // ~symfony/templates/
//		'cache',         // ~symfony/cache/
//		'logs',          // ~symfony/logs/
//	];

    /**
     * Returns the {@see App\Kernel} instance from the {@see ContainerInstance}.
     *
     * @return KernelInterface {@see App\Kernel}
     */
    protected static function kernel() : KernelInterface {
        return ContainerInstance::getService( 'kernel' );
    }

    /**
     * @param  ?string  $get  {@see ParameterBagInterface::get}
     *
     * @return ParameterBagInterface | string | null
     */
    protected static function parameterBag( ?string $get = null ) : ParameterBagInterface | string | null {

        $parameterBag = ContainerInstance::getService( 'parameter_bag' );

        if ( null === $get ) {
            return $parameterBag;
        }

        try {
            return $parameterBag->get( $get );
        }
        catch ( ParameterNotFoundException $exception ) {
            Log::Alert(
                'Failed getting parameter {get}, the parameter does not exist. Returned raw string:{get} instead.',
                [
                    'get'       => $get,
                    'exception' => $exception,
                ],
            );
            return $get;
        }
    }

    /**
     * Returns the {@see Stopwatch} instance from the {@see ContainerInstance}.
     *
     * @return ?Stopwatch {@see Stopwatch}, or null if the service is not available.
     */
    protected static function stopwatch() : ?Stopwatch {
        return ContainerInstance::getService( 'debug.stopwatch' );
    }
}