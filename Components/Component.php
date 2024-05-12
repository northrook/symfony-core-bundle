<?php

namespace Northrook\Symfony\Components;

use Northrook\Core\Get;use Northrook\Core\Interface\Printable;use Northrook\Elements\Element;use Northrook\Elements\Element\Attribute;use Northrook\Elements\Render\Template;use Northrook\Support\Html;use Northrook\Symfony\Core\DependencyInjection\CoreDependencies;use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @property-read  ?Stopwatch $stopwatch
 *
 *
 * @author  Martin Nielsen <mn@northrook.com>
 */
abstract class Component implements Printable
{

    use Get\ClassNameMethods;

    protected const TAG = 'component';

    protected const CLASSES = null;

    private ?string $string;

    protected string           $id;
    protected readonly string  $className;
    protected readonly Element $component;


    public function __construct(
        protected array                     $data,
        protected readonly CoreDependencies $get,
    ) {
        $this->className = $this->getObjectClassName();
        $this->get->stopwatch?->start( $this->className, 'Component' );

        $this->component = new Element( tag : $this::TAG, class : $this::CLASSES );

        $this->component->template = $this->template();

        $this->assignProperties();
        $this->construct();
    }

    private function assignProperties() : void {

        if ( property_exists( $this, 'id' ) ) {
            $this->id = $this->properties( 'name' );
        }

        foreach ( $this->data[ 'properties' ] ?? [] as $name => $value ) {
            if ( property_exists( $this, $name ) ) {
                $this->$name = $value;
                unset( $this->data[ 'properties' ][$name] );
            }
        }

        $this->component->addAttributes( $this->data[ 'properties' ] ?? [] );
    }

    final protected function properties( string | array $get ) : null | string | array {

        if ( is_string( $get ) ) {
            return $this->data[ 'properties' ][ $get ] ?? null;
        }

        $properties = [];

        foreach ( $get as $key => $value ) {

            $name = is_string( $key ) ? $key : $value;

            if ( is_string( $key ) ) {
                $properties[ $name ] = $this->data[ 'properties' ][ $name ] ?? $value ?? null;
            }
            else {
                $properties[ $value ] = $this->data[ 'properties' ][ $value ] ?? null;
            }

        }

        return $properties;
    }

    final public function data( string $get, bool $preserve = true ) : mixed {
        $data = $this->data[ $get ] ?? null;

        if ( false === $preserve && null !== $data ) {
            unset( $this->data[ $get ] );
        }

        return $data;
    }

    /**
     * @return string The string representation of the {@see Template} used to build the {@see Component}.
     */
    abstract protected function template() : string;

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
     */
    abstract public function build() : void;

    /**
     * @param bool  $pretty
     *
     * @return null|string
     */
    final public function print( bool $pretty = true ) : string {
        $string = $this->__toString();

        return $pretty ? Html::pretty( $string ) : $string;
    }

    /**
     * @return string
     */
    final public function __toString() : string {

        $this->build();

        $this->componentValidation();

        $this->string = $this->component->print( true );

        $this->get->stopwatch?->stop( $this->className );

        if ( !$this->string ) {
            $this->get->logger?->error(
                'Component {className} failed to build.',
                [ 'className' => $this->className ],
            );
            return '';
        }

        return $this->string;
    }

    private function componentValidation() : void {
        if ( !$this->component->has( 'id' ) ) {
            $id = $this->getExtendingClasses() + [ $this->id, 'field' ];
            $this->component->set( 'id', Attribute::id( $id, true ) );
        }
    }

}