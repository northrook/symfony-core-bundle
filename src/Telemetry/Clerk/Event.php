<?php

namespace Northrook\Symfony\Core\Telemetry\Clerk;

use Northrook\Logger\Log;
use Symfony\Component\Stopwatch\Stopwatch;


/**
 * @internal
 */
final class Event
{
    public function __construct(
            public readonly string        $name,
            public readonly ?string       $group = null,
            protected readonly ?Stopwatch $stopwatch,
            bool                          $autoStart = true,
    )
    {
        if ( $autoStart ) {
            $this->stopwatch->start( $this->name, $this->group );
        }
    }

    /**
     * @param null|string  $log  language=Smarty
     * @param array        $context
     *
     * @return void
     */
    public function start( ?string $log = null, array $context = [] ) : void
    {
        if ( $log ) {
            Log::notice( $log );
        }
        $this->stopwatch->start( $this->name, $this->group );
    }

    public function isActive() : bool
    {
        return $this->stopwatch->isStarted( $this->name );
    }
}