<?php

namespace Northrook\Symfony\Components\Input;

use Northrook\Symfony\Components\Input;

class Password extends Input
{

    public function build() : ?string {
        return $this->className;
    }
}