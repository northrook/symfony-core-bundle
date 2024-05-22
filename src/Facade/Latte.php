<?php

namespace Northrook\Symfony\Core\Facade;

use Northrook\Symfony\Core\DependencyInjection\Facade;

/**
 * @method static string render( string $template, object | array | null $parameters = null )
 */
final class Latte extends Facade
{
    public const SERVICE = \Northrook\Symfony\Latte\Environment::class;

}