<?php

namespace Northrook\Symfony\Core\Components;

use Northrook\Symfony\Core\Latte\Component;

/**
 * # `<field:text>`
 */
class Input extends Component
{

    public function build() : string {
        return '<input type="text" name="name" value="value" />';
    }
}