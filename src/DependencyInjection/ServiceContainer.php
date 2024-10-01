<?php

declare(strict_types=1);

namespace Northrook\Symfony\Core\DependencyInjection;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Exception;
use LogicException;

final class ServiceContainer
{
    private static ServiceLocator $serviceLocator;

    /**
     * @param ?ServiceLocator $serviceLocator
     */
    public function __construct( ?ServiceLocator $serviceLocator )
    {
        $this::$serviceLocator ??= $serviceLocator;
    }

    /**
     * @template Service
     *
     * @param class-string<Service> $get
     *
     * @return Service
     */
    public static function get( string $get ) : mixed
    {
        if ( ServiceLocator::class === $get ) {
            return ServiceContainer::$serviceLocator;
        }

        try {
            return ServiceContainer::$serviceLocator->get( $get );
        }
        catch ( Exception $exception ) {
            throw new LogicException( message  : "The '".ServiceContainer::class."' does not provide access to the '".$get::class."' service.", code     : 500, previous : $exception);
        }
    }
}
