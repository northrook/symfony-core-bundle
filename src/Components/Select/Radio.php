<?php

namespace Northrook\Symfony\Core\Components\Select;

use Northrook\Elements\Icon;
use Northrook\Elements\Render\Template;
use Northrook\Symfony\Core\Latte\Component;
use Northrook\Symfony\Core\Latte\Component\FieldStructureTrait;

/**
 * # `<field:select type="radio">`
 *
 * ```
 * <field>
 *
 * </field>
 * ```
 */
class Radio extends Component
{
    use FieldStructureTrait;

    public function build() : string {
        $this->template = new Template(
            <<<HTML
            {input}{label} 
        HTML,
        );

        $this->field->class->add( $this->properties->id, $this->properties->name );

        $this->input->set( 'type', 'checkbox' );

        $this->label->content = [
            'indicator' => '<i class="indicator">' . Icon::checkmark() . '</i>',
            'label'     => '<span>' . $this->label->content . '</span>',
        ];

        return ( string ) $this->field();
    }
}