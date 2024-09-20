<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Telemetry;

use Northrook\Symfony\Core\DependencyInjection\ServiceContainer;
use Northrook\Symfony\Core\Telemetry\Clerk\ClerkEvent;
use Northrook\Trait\SingletonClass;
use Symfony\Component\Stopwatch\Stopwatch;


final class Clerk
{

    use SingletonClass;


    private readonly ?Stopwatch $stopwatch;

    protected array $events = [];

    public function __construct( ?Stopwatch $stopwatch = null )
    {
        $this->stopwatch = $stopwatch ?? ServiceContainer::get( Stopwatch::class );
        $this::$instance = $this;
    }

    public function event( string $name, ?string $group = null, bool $autoStart = true ) : ClerkEvent
    {
        return $this->events[ $name ] ??= new ClerkEvent( $name, $group, $this->stopwatch, $autoStart );
    }

    public static function monitor( string $event, ?string $group = null, bool $autoStart = true ) : ClerkEvent
    {
        return Clerk::$instance->event( $event, $group, $autoStart );
    }

    public function getEvents() : array
    {
        return $this->events;
    }

}