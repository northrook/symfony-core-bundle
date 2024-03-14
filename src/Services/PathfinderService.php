<?php

namespace Northrook\Symfony\Core\Services;

use Northrook\Symfony\Core\Path;
use Northrook\Types as Type;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PathfinderService
{

    /**
     * @var Path[]
     */
    private array $parameterCache = [];

    public function __construct(
        private readonly ParameterBagInterface $parameter,
        private readonly ?LoggerInterface      $logger = null,
    ) {}

    /**
     * @param string  $path  {@see ParameterBagInterface::get}
     *
     * @return Type\Path
     *
     */
    public function get(
        string  $path,
        ?string $add = null,
    ) : Type\Path {
        return Path::get( $path );
    }
}