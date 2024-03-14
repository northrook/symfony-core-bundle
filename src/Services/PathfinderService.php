<?php

namespace Northrook\Symfony\Core\Services;

use Northrook\Symfony\Core\File;
use Northrook\Types\Path;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PathfinderService
{

    /**
     * @var File[]
     */
    private array $parameterCache = [];

    public function __construct(
        private readonly ParameterBagInterface $parameter,
        private readonly ?LoggerInterface      $logger = null,
    ) {}

    /**
     * @param string  $path  {@see ParameterBagInterface::get}
     *
     * @return Path
     *
     */
    public function get(
        string  $path,
        ?string $add = null,
    ) : Path {
        return File::get( $path );
    }
}