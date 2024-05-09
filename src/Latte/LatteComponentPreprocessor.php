<?php

namespace Northrook\Symfony\Core\Latte;

use Northrook\Support\Html;
use Northrook\Symfony\Components\Input;
use Northrook\Symfony\Core\DependencyInjection\CoreDependencies;
use Northrook\Symfony\Latte\Preprocessor\Preprocessor;

/**
 * # Field Component Preprocessor
 *
 * * Matches `<field:component ... >` tags in the {@see $content} string.
 * * Processes and replaces the matched components in the {@see $content} string.
 */
final class LatteComponentPreprocessor extends Preprocessor
{

    private const ELEMENNTS = [
        // 'button' => Components\Button::class,
        // 'icon'   => Elements\Icon::class,
    ];

    private const COMPONENTS = [
        'field:password' => Input\Password::class,

        // 'field:text'     => Input::class,
        // 'field:email'    => Input\Email::class,
        // 'field:password' => Input\Password::class,
        // 'field:checkbox' => Input\Checkbox::class,
        // 'field:toggle'   => Input\Toggle::class,
        // 'field:textarea' => TextArea::class,
    ];

    /**
     * A list of all matched components in the {@see $content} string.
     */
    private array $components = [];

    public function __construct(
        private readonly CoreDependencies $get,
    ) {
        // dump( __METHOD__);
    }

    public function process() : self {

        // dump( 1);

        $this->prepareContent( false )
             ->matchFields()
             ->proccessElements();

        foreach ( $this->components as $name => $components ) {

            if ( !isset( LatteComponentPreprocessor::COMPONENTS[ $name ] ) || empty( $components ) ) {
                continue;
            }

            /** @var \Northrook\Symfony\Components\Component $component */
            $class = LatteComponentPreprocessor::COMPONENTS[ $name ];

            if ( !is_subclass_of( $class, \Northrook\Symfony\Components\Component::class ) ) {
                $this->logger->error(
                    message : "{object} is not a subclass of {component}.",
                    context : [
                                  '$object'   => $class,
                                  'component' => $class::class,
                              ],
                );
                continue;
            }

            foreach ( $components as $data ) {
                $component = new ( $class )( $data, $this->get );

                $this->updateContent( $component->data( 'source', true ), $component->print( true ) );
                // dump( $component );
            }
        }

        return $this;
    }

    private function matchFields() : self {

        $count = preg_match_all(
        /** @lang PhpRegExp */
            pattern : '/<(?<component>(\w*?):.*?)>/ms',
            subject : $this->content,
            matches : $fields,
            flags   : PREG_SET_ORDER,
        );

        if ( !$count ) {
            return $this;
        }

        foreach ( $fields as $element ) {
            $component = $this->getComponentNamespace( $element[ 'component' ] );
            [ $tag, $type ] = explode( ':', $component, 2 );
            $source = $element[ 0 ];

            if ( str_contains( $this->content, "</$component>" )
                 && false === str_ends_with( trim( $source ), '/>' ) ) {

                preg_match( "/<$component.*?>.*?<\/$component>/ms", $this->content, $closingTag );

                $source = $closingTag[ 0 ];
            }


            $this->components[ $component ][] = [
                'source'     => $source,
                'properties' => Html::extractAttributes( $element[ 0 ] ),
                'tag'        => $tag,
                'type'       => $type,
            ];
        }


        return $this;
    }

    private function proccessElements() : void {

        foreach ( LatteComponentPreprocessor::ELEMENNTS as $tag => $parser ) {

            $count = preg_match_all(
            /** @lang PhpRegExp */
                pattern : "/<(?<component>$tag).*?>/ms",
                subject : $this->content,
                matches : $elements,
                flags   : PREG_SET_ORDER,
            );

            if ( !$count ) {
                return;
            }

            foreach ( $elements as $element ) {
                $source                     = $element[ 0 ];
                $parser                     = new( $parser )( ... $this->getComponentProperties( $source ) );
                $parser->tag->isSelfClosing = str_ends_with( $source, '/>' );

                $this->updateContent( $source, $parser->print() );
            }
        }
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