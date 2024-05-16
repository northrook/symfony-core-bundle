<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Facade;

use Northrook\Symfony\Core\DependencyInjection\Facade;
use Symfony\Component\Stopwatch as Symfony;
use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * @method static bool isStarted( string $name )
 * @method static StopwatchEvent start( string $name, ?string $category = null )
 * @method static StopwatchEvent stop( string $name )
 * @method static StopwatchEvent lap( string $name )
 * @method static StopwatchEvent getEvent( string $name )
 * @method static StopwatchEvent[] getSectionEvents( string $id )
 */
final class Stopwatch extends Facade
{
    protected const SERVICE = Symfony\Stopwatch::class;
}