<?php

namespace Northrook\Symfony\Core\Components\Input;

use Northrook\Elements\Element;
use Northrook\Elements\Icon;
use Northrook\Elements\Render\Template;
use Northrook\Symfony\Core\Latte\Component;
use Northrook\Symfony\Core\Latte\Component\FieldStructureTrait;

/**
 * # `<field:password>`
 */
class Password extends Component
{
    use FieldStructureTrait;

    public function build() : string {
        $this->content = new Template(
            <<<HTML
            <div class="label"> {label} </div>
            <div class="input"> {input} {reveal}</div>
        HTML,
        );

        $this->content->data[ 'reveal' ] = $this->revealPassword();

        $this->field->class->add( $this->properties->id, $this->properties->name );


        return ( string ) $this->field();
    }

    private function revealPassword() : string {

        $icon = Icon::get( 'reveal-password:ui' );

        $keybind = Element::keybind( 'alt+R' );
        $tooltip = "<tooltip>Reveal Password$keybind</tooltip>";

        return <<<HTML
			<button
				type="button"
				class="reveal-password"
				role="switch"
				aria-checked="false"
				tooltip-placement="top"
			>
				$icon
				$tooltip
			</button>
		HTML;
    }
}