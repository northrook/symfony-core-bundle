<?php

namespace Northrook\Symfony\Core\Latte;

use DOMDocument;
use Northrook\Elements\Element;
use Northrook\Support\Get;
use Northrook\Support\Str;
use Northrook\Symfony\Core\Latte\Component\Properties;
use Psr\Log\LoggerInterface;
use Stringable;
use Symfony\Component\Stopwatch\Stopwatch;

abstract class Component implements Stringable
{
    protected const TAG = 'field';

    /** @var string[] | Element[] */
    protected array $component = [];

    /** Raw properties from the template. */
    protected readonly Component\Properties $properties;

    /** @var string The final {@see Component} rendered to string. */
    public readonly string $string;


    /**
     * @param string                $content
     * @param Properties|array      $properties
     * @param null|string           $type
     * @param string                $tag
     * @param null|LoggerInterface  $logger
     * @param null|Stopwatch        $stopwatch
     */
    public function __construct(
        public readonly string              $content,
        Component\Properties | array        $properties,
        protected readonly ?string          $type = null,
        protected string                    $tag = self::TAG,
        protected readonly ?LoggerInterface $logger = null,
        protected readonly ?Stopwatch       $stopwatch = null,
    ) {
        $this->stopwatch->start( Get::className(), 'Component' );

        $this->properties = is_array( $properties ) ? new Component\Properties( $properties ) : $properties;

        $this->construct();
    }

    /**
     * Void method run at the end of {@see __construct()} when the {@see Component} is instantiated.
     *
     * @return void
     */
    protected
    function construct() : void {}

    /**
     * Build the {@see Component}.
     *
     * This method is not intended to be called directly,
     * as it is called by the {@see renderComponentString}
     * whenever the {@see Component} is {@see print}ed or rendered {@see __toString}.
     *
     * This method _should_:
     * * Be the final method in the chain.
     * * Return the {@see Component} in string format.
     * * Not be called directly.
     *
     * The above guidelines can be bent, I'm not the boss of you.
     *
     * @return string
     */
    abstract protected function build() : string;

    /**
     * Assembles the {@see Component} into a string.
     *
     * * Will use {@see Component::$component} unless provided an array.
     *
     * @param null|array   $component
     * @param null|string  $separator
     *
     * @return string
     */
    final protected function assemble( ?array $component = null, ?string $separator = PHP_EOL ) : string {
        return implode( $separator ?? '', $component ??= $this->component );
    }

    /**
     * Returns the {@see Component::$string} if previously set.
     *
     * Otherwise, it calls {@see build()}, stores the result in {@see $string} and returns it.
     *
     * @return string
     */
    final protected function renderComponentString() : string {
        $this->string ??= $this->build();
        $this->stopwatch->stop( Get::className() );
        return $this->string;
    }

    /**
     * Returns the {@see Component} as string, with formatting options.
     *
     * @param bool  $pretty
     *
     * @return string
     *
     * @todo [low] Add more options.
     *
     */
    final public function print( bool $pretty = false ) : string {
        return $this->renderComponentString();
    }

    /**
     * Returns the {@see Component} as string.
     *
     * @return string
     */
    final public function __toString() : string {
        return $this->renderComponentString();
    }


    public static function extractAttributes( string $html ) : array {

        if ( !$html ) {
            return [];
        }

        $html = Str::squish( $html );

        if ( false === str_starts_with( $html, '<' ) && false === str_starts_with( $html, '>' ) ) {
            $html = "<div $html>";
        }
        else {
            $html = strstr( $html, '>', true ) . '>';
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

        return $attributes;
    }
}