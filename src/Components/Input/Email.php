<?php

namespace Northrook\Symfony\Core\Components\Input;

use Northrook\Symfony\Core\Latte\Component;

/**
 * # `<field:email>`
 */
class Email extends Component
{

    public function build() : string {
        dump( $this );

        return '<input type="email" name="name" value="value" />';
    }
}