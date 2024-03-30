<?php

namespace Northrook\Symfony\Core\Services;

use Northrook\Favicon\FaviconBundle;

class FaviconGenerationService
{

    public readonly FaviconBundle $generator;

    public function __construct(
        private readonly ?Stopwatch $stopwatch = null,
    ) {
        $this->generator = new FaviconBundle();
    }
}