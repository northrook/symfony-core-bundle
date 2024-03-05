<?php

namespace Northrook\Symfony\Core\Components;

use Northrook\Components\Asset;
use Northrook\Components\Element;
use Northrook\Components\Icon;
use Northrook\Components\Image;
use Northrook\Symfony\Latte\Preprocessor;
use Northrook\Support\Regex;
use Northrook\Support\Str;
use Northrook\Symfony\Latte\Services\CompileException;

//use Northrook\Components\Element;

final class LatteComponentPreprocessor extends Preprocessor
{
	private mixed $components = [];

	public function construct() : void {
		$this->prepareContent();
		$this->matchComponents();
		$this->processButtons();
		$this->processIcons();
//		dd( $this );
	}

	/**
	 * @throws CompileException
	 */
	private function matchComponents() : void {

		$components = Regex::matchNamedGroups(
			pattern         : "/<(?<component>(?<tag>\w*?):(?<type>\w.*?)) .*?>/ms",
			subject         : $this->content,
			matchedProperty : 'string',
		);

		if ( !$components ) {
			return;
		}

		foreach ( $components as $match ) {

			$this->components = new Component(
				tag : 'field'
			);

			$tag = str_replace( ':', '-', $match->component );

			$test = str_replace( $match->component, $tag, $match->string );

			$node = [
				'component'  => $match->component,                      // ✅
				'tag'        => $match->tag,                            // ✅
				'type'       => $match->type,                           // ✅
				'attributes' => self::extractAttributes( $test, $tag ), // ✅
				'innerHTML'  => null,
				'match'      => $match->string, // ✅
			];

			if (
				false === str_ends_with( $match->string, '/>' )
				&&
				false !== preg_match( "/<\s*?\/\s*?$match->component\s*?>/ms", $this->content, $closingTag )
			) {

				$closingTag = $closingTag[ 0 ] ?? false;

				if ( $closingTag === false ) {
					throw new CompileException(
						"Closing tag expected, but not found for $match->component.\n\nPlease check your template, and either self-close the component, or add a closing tag.",
						$match->string
					);
				}

				$component = strpos( $this->content, $match->string );
				$closing = strpos( $this->content, $closingTag );

				$outerHTML = substr(
					string : $this->content,
					offset : $component,
					length : $closing - $component + strlen( $closingTag ),
				);
				$innerHTML = substr(
					$outerHTML,
					strlen( $match->string ),
					(
						strlen( $outerHTML )
						- strlen( $match->string )
						- strlen( $closingTag )
					),
				);

				$innerHTML = trim( $innerHTML );
				$this->content = str_ireplace( $outerHTML, $match->string, $this->content );

				$node[ 'innerHTML' ] = $innerHTML;
			}

			$this->components[] = $node;
		}
		dd( $this->components );
	}

	private function processButtons() : void {

		if ( !str_contains( $this->content, '<button ' ) ) {
			return;
		}

		$this->content = preg_replace_callback(
			pattern  : "/<button(.*?)>/ms",
			callback : static function ( $element ) : string {

				$button = new Element(
					tag   : 'button',
					close : str_ends_with( $element[ 0 ], '/>' ),
				);

				$button->extractAttributes( trim( $element[ 1 ], ' /' ) );

				if ( $button->has( 'icon' ) ) {
					$button->class->add( 'icon' );
					$button->content[] = Asset::svg( $button->get( 'icon' ) );
//					dd( $button);
					$button->remove( 'icon' );
				}

				if ( $button->has( 'text' ) ) {
					$button->content[] = $button->get( 'text' );
					$button->remove( 'text' );
				}

				return $button->print();
			},
			subject  : $this->content,
		);
	}

	private function processIcons() : void {

		if ( !str_contains( $this->content, '<icon ' ) ) {
			return;
		}

		$this->content = preg_replace_callback(
			pattern  : " /<icon(.*?)>/ms",
			callback : static function ( $element ) : string {

				$icon = Icon::asset(
					$attributes[ 'get' ] ?? null,
					$attributes[ 'stroke' ] ?? null,
				)->extractAttributes( trim( $element[ 1 ], ' /' ) );


//				$attributes = trim( $element[ 1 ], ' /' );

//				$attributes = LatteComponentPreprocessor::extractAttributes( "<icon $attributes > " );
//
//				$get = $attributes[ 'get' ] ?? 'angry';
//				unset( $attributes[ 'get' ] );
//
//				if ( isset( $attributes[ 'class' ] ) ) {
//					$attributes[ 'class' ] = 'icon ' . implode( ' ', (array) $attributes[ 'class' ] );
//				}
//				else {
//					$attributes[ 'class' ] = 'icon';
//				}
//
//				$icon = new Element(
//					tag        : 'i',
//					attributes : $attributes,
//					content    : Asset::icon( $get, raw : true ),
//				);

//				dd( $icon );
				return $icon->print();
			},
			subject  : $this->content,
		);
	}

	private function images() : void {

		if ( !str_contains( $this->content, '<image ' ) ) {
			return;
		}

		$this->content = preg_replace_callback(
			pattern  : "/<image(.*?)>/ms",
			callback : static function ( $element ) : string {

//				$attributes = trim( $element[ 1 ], ' /' );
//				$attributes = static::extractAttributes( $attributes );

//				$isExternal = Str::isUrl( $attributes[ 'src' ] ?? '' );

				$image = new Image();
				$image->extractAttributes( trim( $element[ 1 ], ' /' ) );

				// dd( $image, $image->figure() );

				return $image->print();

				// $get = $attributes['get'] ?? 'angry';
				// unset( $attributes['get'] );

				// if ( isset( $attributes['class'] ) ) {
				// 	$attributes['class'] = 'icon ' . implode( ' ', (array) $attributes['class'] );
				// } else {
				// 	$attributes['class'] = 'icon';
				// }

				// $icon = new Element( 'i', $attributes, Get::icon( $get, raw: true ) );

				// return Str::squish( $icon );
			},
			subject  : $this->content,
		);
	}

	private static function extractAttributes( string $html, ?string $tag = null ) : array {

		if ( !$html ) {
			return [];
		}

		if ( false === str_starts_with( $html, '<' ) && false === str_starts_with( $html, '>' ) ) {
			$tag ??= 'div';
			$html = "<$tag $html > ";
		}

		$tag ??= substr( $html, 1, strpos( $html, ' ' ) - 1 );
		$dom = new \DOMDocument();
		$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR );

		$attributes = [];

		$node = $dom->getElementsByTagName( $tag )->item( 0 );

		foreach ( $node->attributes as $attribute ) {
			$attributes[ $attribute->nodeName ] = $attribute->nodeValue;
		}

		return $attributes;
	}

}