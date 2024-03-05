<?php

namespace Northrook\Symfony\Core\Components;

use Northrook\Components\Element;

class Component extends Element
{

	public static function element( array $match ) : static {
		dump( $match );
		return new static( 'field' );
	}

}