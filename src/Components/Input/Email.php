<?php

namespace Northrook\Symfony\Core\Components\Input;

use Northrook\Elements\Render\Template;
use Northrook\Symfony\Core\Latte\Component;
use Northrook\Symfony\Core\Latte\Component\FieldStructureTrait;

/**
 * # `<field:email>`
 *
 * ```
 * <field>
 *
 * </field>
 * ```
 */
class Email extends Component
{
    use FieldStructureTrait;

    public function build() : string {
        $this->content = new Template(
            <<<HTML
            <div class="label"> {label} </div>
            <div class="input"> {input} </div>
        HTML,
        );

        $this->field->class->add( $this->properties->id, $this->properties->name );


        return ( string ) $this->field();
    }
}