<?php

declare ( strict_types = 1 );

namespace Northrook\Symfony\Core\Service;

use Northrook\CSS\Stylesheet;
use Northrook\Symfony\Core\Autowire\Pathfinder;

final class StylesheetGenerator
{
    public function __construct(
        public readonly Stylesheet  $stylesheet,
        private readonly Pathfinder $pathfinder,
    ) {}
}