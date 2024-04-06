<?php

namespace Northrook\Symfony\Core\Latte;

use DOMDocument;
use Northrook\Support\Str;

final readonly class ProcessElement
{

    /** `tag:type` */
    public string $component;
    public string $tag;
    public string $type;
    public array  $properties;
    public string $source;

    public function __construct(
        array $matched,
    ) {
        $this->component = $this->getComponentNamespace( $matched[ 'component' ] ?? '' );
        [ $this->tag, $this->type ] = explode( ':', $this->component, 2 );
        $this->properties = $this->extractAttributes( $matched[ 0 ] );
        $this->source     = $matched[ 0 ];
    }

    private function getComponentNamespace( string $string ) : string {
        if ( str_contains( $string, ' ' ) ) {
            $string = explode( ' ', $string, 2 )[ 0 ];
        }

        return trim( $string );
    }


    private function extractAttributes( string $html ) : array {

        if ( !$html ) {
            return [];
        }

        $html = Str::squish( $html );

        if ( false === str_starts_with( $html, '<' ) && false === str_starts_with( $html, '>' ) ) {
            $html = "<div $html>";
        }
        else {
            $html = preg_replace(
                pattern     : '/^<(\w.+):\w+? /',
                replacement : '<$1 ',
                subject     : $html,
            );
        }

        $tag ??= substr( $html, 1, strpos( $html, ' ' ) - 1 );
        $dom = new DOMDocument();
        $dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR );

        $attributes = [];

        $node = $dom->getElementsByTagName( $tag )->item( 0 );

        if ( !$node ) {
            return $attributes;
        }

        foreach ( $node->attributes as $attribute ) {
            $attributes[ $attribute->nodeName ] = $attribute->nodeValue;
        }

        // dd(
        //     $html,
        //     $tag,
        //     $dom
        // );
        return $attributes;
    }
}