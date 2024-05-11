<?php

namespace Northrook\Symfony\Components\Input;

use Northrook\Elements\Asset\Icon;use Northrook\Elements\Element;use Northrook\Symfony\Components\Input;

class Checkbox extends Input {

    protected const CLASSES = [ 'checkbox', 'field' ];

    protected string  $id;
    protected string  $name;
    protected ?string $value = null;
    protected string  $label;
    protected bool    $required = false;

    public function build() : void {

        $label  = Element::label(
            for     : $this->id,
            content: [
            'indicator' => '<i class="indicator">' . Icon::checkmark() . '</i>',
            'label'     => '<span>' . $this->label . '</span>',
            ],
        );

        $input  = Element::input(
            type         : 'checkbox',
            id           : $this->id,
            name         : $this->name,
            value        : $this->value,
            required     : $this->required,
        );

        $this->component->class->add( 'field', 'checkbox' );

        $this->component->content( [ 'label' => $label ] )
                        ->content( [ 'input' => $input ] );
    }

    protected function template() : string {
        return <<<HTML
            {input}{label} 
        HTML;
    }
}