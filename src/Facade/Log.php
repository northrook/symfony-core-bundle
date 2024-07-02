<?php

namespace Northrook\Symfony\Core\Facade;

use Northrook\Symfony\Core\DependencyInjection\Facade;

/**
 * @method static void emergency( string|\Stringable $message, array $context = [] )
 * @method static void alert( string|\Stringable $message, array $context = [] )
 * @method static void critical( string|\Stringable $message, array $context = [] )
 * @method static void error( string|\Stringable $message, array $context = [] )
 * @method static void warning( string|\Stringable $message, array $context = [] )
 * @method static void notice( string|\Stringable $message, array $context = [] )
 * @method static void info( string|\Stringable $message, array $context = [] )
 * @method static void debug( string|\Stringable $message, array $context = [] )
 * @method static void log( $level, string|\Stringable $message, array $context = [] )
 */
final class Log extends Facade
{
    protected const SERVICE = \Psr\Log\LoggerInterface::class;
}