<?php

namespace Northrook\Symfony\Core\Latte;

use Northrook\Symfony\Latte\Core;

final class Environment extends Core\Environment
{
    public function __construct() {
        $this->addPreprocessor(
            new LatteComponentPreprocessor(),
        );
    }
}