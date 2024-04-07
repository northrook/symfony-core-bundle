<?php

namespace Northrook\Symfony\Core\Latte;

use JetBrains\PhpStorm\NoReturn;
use Northrook\Symfony\Core\Components\Button;
use Northrook\Symfony\Core\Components\Input;
use Northrook\Symfony\Latte\Preprocessor\Preprocessor;

/**
 * # Field Component Preprocessor
 *
 * * Matches `<field:component ... >` tags in the {@see $content} string.
 * * Processes and replaces the matched components in the {@see $content} string.
 */
final class LatteComponentPreprocessor extends Preprocessor
{

    private const COMPONENTS = [
        'button'         => Button::class,
        'field:text'     => Input::class,
        'field:email'    => Input\Email::class,
        'field:password' => Input\Password::class,
        // 'field:textarea' => TextArea::class,

    ];

    /**
     * A list of all matched components in the {@see $content} string.
     */
    private array $components = [];

    public function __construct() {}


    #[NoReturn]
    public function process() : self {

        $this->prepareContent( false )
             ->matchElements();

        foreach ( $this->components as $name => $components ) {

            if ( !isset( LatteComponentPreprocessor::COMPONENTS[ $name ] ) || empty( $components ) ) {
                continue;
            }

            /** @var Component $component */
            $class = LatteComponentPreprocessor::COMPONENTS[ $name ];

            if ( !is_subclass_of( $class, Component::class ) ) {
                $this->logger->error(
                    message : "{object} is not a subclass of {component}.",
                    context : [
                                  '$object'   => $class,
                                  'component' => Component::class,
                              ],
                );
                continue;
            }

            foreach ( $components as $data ) {

                $component = new ( $class )(
                    $data[ 'source' ],
                    $data[ 'properties' ],
                    $data[ 'type' ],
                    $data[ 'tag' ],
                    $this->logger,
                    $this->stopwatch,
                );

                $this->updateContent( $component->source, $component->print( true ) );
            }
        }

        return $this;
    }

    private function matchElements() : self {

        $count = preg_match_all(
                    "/<(?<component>(\w*?):.*?)>/ms",
                    $this->content,
                    $matches,
            flags : PREG_SET_ORDER,
        );

        if ( !$count ) {
            return $this;
        }

        foreach ( $matches as $element ) {
            $component = $this->getComponentNamespace( $element[ 'component' ] );
            [ $tag, $type ] = explode( ':', $component, 2 );
            $source = $element[ 0 ];

            if ( str_contains( $this->content, "</$component>", )
                 && false === str_ends_with( trim( $source ), '/>', ) ) {

                preg_match( "/<$component.*?>.*?<\/$component>/ms", $this->content, $closingTag );

                $source = $closingTag[ 0 ];
            }


            $this->components[ $component ][] = [
                'source'     => $source,
                'properties' => $this->getComponentProperties( $element[ 0 ] ),
                'tag'        => $tag,
                'type'       => $type,
            ];
        }


        return $this;
    }

    private function getComponentProperties( string $source ) : Component\Properties {
        return new Component\Properties( Component::extractAttributes( $source ) );
    }

    private function getComponentNamespace( string $string ) : string {
        if ( str_contains( $string, ' ' ) ) {
            $string = explode( ' ', $string, 2 )[ 0 ];
        }

        return trim( $string );
    }


}