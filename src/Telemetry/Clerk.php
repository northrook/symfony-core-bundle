<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Telemetry;

use Northrook\Symfony\Core\DependencyInjection\ServiceContainer;
use Northrook\Symfony\Core\Telemetry\Clerk\Event;
use Northrook\Trait\SingletonClass;
use Symfony\Component\Stopwatch\Stopwatch;


final class Clerk
{

    use SingletonClass;


    private readonly ?Stopwatch $stopwatch;

    protected array $events = [];

    private function __construct( ?Stopwatch $stopwatch = null )
    {
        dump( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 2 ) );
        $this->stopwatch = $stopwatch ?? ServiceContainer::get( Stopwatch::class );
        $this::$instance = $this;
    }

    private function event( string $name, ?string $group = null, bool $autoStart = true ) : Event
    {
        return $this->events[ $name ] ??= new Event( $name, $group, $this->stopwatch, $autoStart );
    }

    public static function monitor( string $event, ?string $group = null, bool $autoStart = true ) : Event
    {
        return Clerk::$instance->event( $event, $group, $autoStart );
    }

}