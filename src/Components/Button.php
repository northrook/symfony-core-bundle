<?php

namespace Northrook\Symfony\Core\Components;

use Northrook\Symfony\Core\Latte\Component;

class Button extends Component
{

    public function build() : string {
        return '<button type="submit" class="btn btn-primary">Submit</button>';
    }
}