<?php

namespace Northrook\Symfony\Core\Components\Input;

use Northrook\Symfony\Core\Latte\Component;

/**
 * # `<field:password>`
 */
class Password extends Component
{

    public function build() : string {
        return '<input type="password" name="name" value="value" />';
    }
}