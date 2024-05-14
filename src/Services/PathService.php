<?php

namespace Northrook\Symfony\Core\Services;

use Psr\Log\LoggerInterface;use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;use Symfony\Contracts\Cache\CacheInterface;


final readonly class PathService {


    public function __construct(
        private  ParameterBagInterface $parameter,
        private  CacheInterface $cache,
        private  ?LoggerInterface      $logger = null,
    ) {}

    public function test( string $path = '' ) : string {
        return $path;
        }
}