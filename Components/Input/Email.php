<?php

namespace Northrook\Symfony\Components\Input;

use Northrook\Elements\Element;use Northrook\Symfony\Components\Input;

class Email extends Input {

    protected const CLASSES = [ 'email', 'field' ];

    protected string  $id    = 'name';
    protected string  $name;
    protected ?string $value = null;
    protected string  $label;
    protected bool    $required;

    public function build() : void {

        $label  = Element::label(
            for     : $this->id,
            content : $this->label,
        );
        $input  = Element::input(
            type         : 'email',
            id           : $this->id,
            name         : $this->name,
            value        : $this->value,
            autocomplete : $this->properties( 'autocomplete' ),
            required     : $this->required,
        );

        $this->component->class->add( 'field', 'email' );

        $this->component->content( [ 'label' => $label ] )
                        ->content( [ 'input' => $input ] );
    }

    protected function template() : string {
        return <<<HTML
            <div class="label">{label}</div>
            <div class="input">{input}</div>
        HTML;
    }
}