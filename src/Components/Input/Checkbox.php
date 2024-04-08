<?php

namespace Northrook\Symfony\Core\Components\Input;

use Northrook\Elements\Icon;
use Northrook\Elements\Render\Template;
use Northrook\Symfony\Core\Latte\Component;
use Northrook\Symfony\Core\Latte\Component\FieldStructureTrait;

/**
 * # `<field:checkbox>`
 *
 * ```
 * <field>
 *
 * </field>
 * ```
 */
class Checkbox extends Component
{
    use FieldStructureTrait;

    public function build() : string {
        $this->content = new Template(
            <<<HTML
            {input}{label} 
        HTML,
        );

        $this->field->class->add( $this->properties->id, $this->properties->name );


        $this->label->content(
            [
                'indicator' => '<i class="indicator">' . Icon::checkmark() . '</i>',
                'label'     => '<span>' . $this->label->content . '</span>',
            ],
        );

        return ( string ) $this->field();
    }
}