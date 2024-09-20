<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Telemetry;

use Northrook\Symfony\Core\DependencyInjection\ServiceContainer;
use Northrook\Symfony\Core\Telemetry\Clerk\ClerkEvent;
use Northrook\Trait\SingletonClass;
use Symfony\Component\Stopwatch\Stopwatch;


/**
 * @author Martin Nielsen <mn@northrook.com>
 */
final class Clerk
{

    use SingletonClass;


    private readonly ?Stopwatch $stopwatch;

    protected array $groups = [];
    protected array $events = [];

    public function __construct( ?Stopwatch $stopwatch = null )
    {
        $this->instantiationCheck();
        $this->stopwatch = $stopwatch ?? ServiceContainer::get( Stopwatch::class );
        $this::$instance = $this;
    }

    public static function event( string $name, ?string $group = null, bool $autoStart = true ) : ClerkEvent
    {
        return Clerk::getInstance( true )->getEvent( $name, $group, $autoStart );
    }

    public static function stop( string $name ) : void
    {
        Clerk::getInstance( true )->getEvent( $name, autoStart : false )->stop();
    }

    public static function stopGroup( string $name ) : void
    {
        Clerk::getInstance( true )->stopGroupEvents( $name );
    }

    /**
     * @param string       $name
     * @param null|string  $group
     * @param bool         $autoStart
     *
     * @return \Northrook\Symfony\Core\Telemetry\Clerk\ClerkEvent
     */
    private function getEvent( string $name, ?string $group = null, bool $autoStart = true ) : ClerkEvent
    {
        if ( \array_key_exists( $name, $this->events ) ) {
            return $this->events[ $name ];
        }

        $event = new ClerkEvent( $name, $group, $this->stopwatch, $autoStart );

        if ( $group ) {
            $this->groups[ $group ][] = $name;
        }

        return $this->events[ $name ] = $event;
    }

    /**
     * @return array<ClerkEvent>
     */
    public function getEvents() : array
    {
        return $this->events;
    }

    private function stopGroupEvents( string $group ) : array
    {
        $stopped = [];
        foreach ( $this->groups[ $group ] ?? [] as $event ) {
            if ( \array_key_exists( $event, $this->events ) ) {
                $this->events[ $event ]->stop();
                $stopped[] = $event;
            }
        }
        return $stopped;
    }

}