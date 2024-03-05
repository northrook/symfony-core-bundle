<?php

namespace Northrook\Symfony\Core\Components;

use Northrook\Components\Element;
use Northrook\Components\Element\Properties;

// TODO: abstract class when we have children to call
class Component extends Element
{

	protected const TAG = 'field';

	public string $field; // input, select, textarea etc
	public string $type;  // email, combobox, editor etc
	protected Properties $properties;


	public static function element( $match ) : static {

		$component = match ( $match->type ) {
			'input' => new Input(),
			default => new self(),
		};

		$component->field = $match->tag;
		$component->type = $match->type;
		$component->extractAttributes(
			         trim( str_replace( $match->component, '', $match->string ), '< />' ),
			return : 'assignProperties',
		);

		dump( $component );
		return $component;
	}

}