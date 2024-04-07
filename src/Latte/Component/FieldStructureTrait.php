<?php

namespace Northrook\Symfony\Core\Latte\Component;

use JetBrains\PhpStorm\Language;
use Northrook\Elements\Field;
use Northrook\Elements\Input;
use Northrook\Elements\Label;
use Northrook\Elements\Render\Template;

trait FieldStructureTrait
{

    public ?string $id;
    public ?string $name;
    public ?string $value = null;
    public bool    $required;

    protected RenderMethod $renderMethod;
    protected Template     $content;
    protected Field        $field;
    protected Input        $input;
    protected Label        $label;

    protected function construct() : void {
        $this->name     = $this->properties?->joink( 'name' );
        $this->id       = $this->properties->joink( 'id' ) ?? $this->name;
        $this->value    = $this->properties->joink( 'value' );
        $this->required = (bool) $this->properties->joink( 'required' );

        $render = $this->properties->joink( 'render' );

        $this->renderMethod = match ( $render ) {
            'static' => RenderMethod::STATIC,
            'live'   => RenderMethod::LIVE,
            default  => RenderMethod::RUNTIME,
        };

        $this->field = new Field(
            id    : "$this->id-field",
            class : $this->properties->joink( 'class' ),
            style : $this->properties->joink( 'style' ),
        );
        $this->input = new Input(
            id           : $this->id,
            name         : $this->name,
            required     : $this->required,
            autofocus    : $this->properties->joink( 'autofocus' ),
            autocomplete : $this->properties->joink( 'autocomplete' ),
        );
        $this->label = new Label(
            for     : $this->id,
            content : $this->properties?->joink( 'label' ),
        );

    }

    final protected function field() : Field {
        $this->content->data[ 'label' ] = $this->label;
        $this->content->data[ 'input' ] = $this->input;

        foreach ( $this->properties as $name => $value ) {
            $this->field->set( $name, $value );
        }

        $this->field->set( 'content', $this->content );


        return $this->field;
    }

}