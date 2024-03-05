<?php

namespace Northrook\Symfony\Core\Components;

use DOMDocument;
use DOMNode;
use JetBrains\PhpStorm\ExpectedValues;
use Northrook\Components\Element\Properties;
use Psr\Log\LoggerInterface;
use stdClass;
use Stringable;
use Symfony\Component\Stopwatch\Stopwatch;

abstract class Component extends stdClass implements Stringable
{
	protected const TAG = 'field';

	protected DOMNode $node;

	protected readonly string  $componentId;
	protected ?LoggerInterface $logger    = null;
	protected ?Stopwatch       $stopwatch = null;

	public readonly string $id;
	public readonly string $field; // input, select, textarea etc
	public readonly string $type;  // email, combobox, editor etc
	protected Properties   $properties;

	private function __construct() {}

	private function assignProperties(
		string         $string,
		string | array $remove = [],
	) : self {

		$string = trim( str_replace( (array) $remove, '', $string ), '< />' );

		if ( !$string ) {
			return $this;
		}

		$tag = $this->tag->name;

		if ( false === str_starts_with( $string, '<' ) && false === str_starts_with( $string, '>' ) ) {
			$string = "<$tag $string > ";
		}

		$dom = new DOMDocument();
		$dom->loadHTML( $string, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR );


		$this->node = $dom->getElementsByTagName( $tag )->item( 0 );

		if ( !$this->node->attributes ) {
			return $this;
		}

		$attributes = [];

		foreach ( $this->node->attributes as $attribute ) {
			$attributes[ $attribute->nodeName ] = $attribute->nodeValue;
		}

		if ( !$attributes ) {
			return $this;
		}


		$this->properties = new Properties( $attributes );

		return $this;
	}


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

		$component->field = $match->tag;
		$component->type = $match->type;
		$component->assignProperties( $match->string, $match->component )
		          ->assignComponentId(
			          $component->field, $component->type, $component->properties->name,
		          )
		;

		$component->logger = $logger;
		$component->stopwatch = $stopwatch;

		$component->stopwatch->start( $component->properties->id, 'component' );

		dump( $component );
		return $component;
	}


	private function assignComponentId( string ...$string ) : self {
		$this->id = implode(
			':', array_filter( $string ),
		) ?: uniqid();

		return $this;
	}

	public function __toString() : string {
		// TODO: Implement __toString() method.
		return '';
	}
}