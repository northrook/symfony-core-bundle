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

        $timeout = $this->properties->joink( 'timeout' ) ?? 3000;

        $this->field->class->add( $this->properties->id, $this->properties->name );
        $this->input->set( 'type', 'password' );
        $this->field->set( 'timeout', $timeout );
        $this->content->data[ 'reveal' ] = $this->revealPassword( $timeout );


        return ( string ) $this->field();
    }

    private function revealPassword( int | string $duration ) : string {

        $tooltip = '<tooltip>Reveal Password' . Element::keybind( 'alt+R' ) . '</tooltip>';
        $icon    = Icon::svg( 'reveal-password:ui', 'indicator' );
        $timeout = Icon::circle( 'timeout' );

        $duration = is_int( $duration ) ? " timeout=\"$duration\"" : '';

        return <<<HTML
			<button type="button" class="reveal-password" role="switch" $duration aria-checked="false" tooltip-placement="top">
				$tooltip
				$icon
			    $timeout
			</button>
		HTML;
    }
}