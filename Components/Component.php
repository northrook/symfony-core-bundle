<?php

namespace Northrook\Symfony\Components;

use Northrook\Core\Get;
use Northrook\Elements\Element;
use Northrook\Symfony\Components\Component\Properties;
use Northrook\Symfony\Core\DependencyInjection\CoreDependencies;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @property-read  ?Stopwatch $stopwatch
 *
 *
 * @author  Martin Nielsen <mn@northrook.com>
 */
abstract class Component implements \Stringable
{

    use Get\ObjectClassName;

    protected const TAG = 'component';

    private ?string $string;

    protected readonly string     $className;
    protected readonly Element    $element;
    protected readonly Properties $properties;

    /** @var string[] | Element[] */
    protected array $component = [];


    public function __construct(
        protected array                     $data,
        protected readonly CoreDependencies $get,
    ) {
        $this->className = $this->getObjectClassName();
        $this->get->stopwatch?->start( $this->className, 'Component' );

        $this->element    = new Element( $this::TAG );
        $this->properties = new Properties();

        $this->construct();
    }

    final protected function data( string $get, bool $preserve = false ) : mixed {
        $data = $this->data[ $get ] ?? null;

        if ( false === $preserve && null !== $data ) {
            unset( $this->data[ $get ] );
        }

        return $data;
    }

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
     * Void method run at the end of {@see __construct()} when the {@see Component} is instantiated.
     *
     * @return void
     */
    abstract protected function construct() : void;

    /**
     * Build the {@see Component}.
     *
     * Called whenever the {@see Component} is {@see print}ed or rendered {@see __toString}.
     *
     * This method _should_:
     * * Be the final method in the chain.
     * * Return the {@see Component} in string format when valid.
     * * Return null on error.
     * * Not be called directly.
     *
     * The above guidelines can be bent, I'm not the boss of you.
     *
     * @return ?string
     */
    abstract public function build() : ?string;

    /**
     * @param bool  $pretty
     *
     * @return null|string
     */
    final public function print( bool $pretty = true ) : ?string {
        return $pretty ? Element\Html::pretty( $this->__toString() ) : null;
    }

    /**
     * @return string
     */
    final public function __toString() : string {

        $string = $this->build();

        $this->get->stopwatch?->stop( $this->className );

        if ( !$string ) {
            $this->get->logger?->error(
                'Component {className} failed to build.',
                [ 'className' => $this->className ],
            );
            return '';
        }

        return $string;
    }

}