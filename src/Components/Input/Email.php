<?php

namespace Northrook\Symfony\Core\Components\Input;

use Northrook\Elements\Field;
use Northrook\Elements\Input;
use Northrook\Elements\Label;
use Northrook\Symfony\Core\Latte\Component;

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

    private Field $field;
    private Input $input;
    private Label $label;

    protected function construct() : void {
        $this->field = new Field();
        $this->input = new Input();
        $this->label = new Label();
    }

    public function build() : string {

        $component = $this->assemble(
            [
                'field' => $this->field,
                'input' => $this->input,
                'label' => $this->label,
            ],
        );

        dd(
            $this,
            $component,
        );

        return $component;
    }
}