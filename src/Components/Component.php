<?php

namespace Northrook\Symfony\Core\Components;

use DOMDocument;
use Northrook\Components\Element\Properties;
use Psr\Log\LoggerInterface;
use stdClass;
use Stringable;
use Symfony\Component\Stopwatch\Stopwatch;

abstract class Component extends stdClass implements Stringable
{
	public readonly string $templateString;

	protected const TAG = 'field';

	protected Properties       $properties;
	protected ?LoggerInterface $logger    = null;
	protected ?Stopwatch       $stopwatch = null;

	public readonly string $id;    // type:field:name
	public readonly string $field; // input, select, textarea etc
	public readonly string $type;  // email, combobox, editor etc

	private function __construct() {}

	public static function element(
		$match,
		?LoggerInterface $logger = null,
		?Stopwatch $stopwatch = null,
	) : static {

		$component = match ( $match->type ) {
			// input
			'password' => new Input\Password(),
			'email'    => new Input\Email(),
			'text'     => new Input\Text(),
			// select
			'combobox' => new Select\Combobox(),
			default    => new static(),
		};

		$component->templateString = $match->string;
		$component->field = $match->tag;
		$component->type = $match->type;
		$component->assignProperties()
		          ->assignComponentId(
			          $component->field,
			          $component->type,
			          $component->properties->name,
		          )
		;

		$component->logger = $logger;
		$component->stopwatch = $stopwatch;
		$component->stopwatch->start( $component->id, 'component' );

		return $component;
	}

	private function assignComponentId( ?string ...$string ) : self {
		$this->id = implode(
			':', array_filter( $string ),
		) ?: uniqid();

		return $this;
	}

	public function print() : string {
		return $this;
	}

	public function __toString() : string {
		$this->stopwatch->stop( $this->id );
		// TODO: Implement __toString() method.

		return '';
	}

	private function assignProperties() : self {

		$string = trim( $this->templateString );

		if ( !$string ) {
			return $this;
		}

		if ( str_starts_with( $string, '<' ) ) {
			$string = '<input' . strstr( $string, ' ' );
		}

		$tag = $this->field;

		if ( false === str_starts_with( $string, '<' ) && false === str_starts_with( $string, '>' ) ) {
			$string = "<$tag $string > ";
		}

		$dom = new DOMDocument();

		$dom->loadHTML( $string, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR );

		$node = $dom->getElementsByTagName( $tag )->item( 0 );

		if ( !$node->attributes ) {
			return $this;
		}

		$attributes = [];

		foreach ( $node->attributes as $attribute ) {
			$attributes[ $attribute->nodeName ] = $attribute->nodeValue;
		}

		if ( !$attributes ) {
			return $this;
		}

		$this->properties = new Properties( $attributes );

		return $this;
	}
}