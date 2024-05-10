<?php

namespace Northrook\Symfony\Core\Latte;

use Northrook\Core\Interface\Printable;use Northrook\Elements\Asset;use Northrook\Support\Html;use Northrook\Symfony\Components;use Northrook\Symfony\Components\Input;use Northrook\Symfony\Core\DependencyInjection\CoreDependencies;use Northrook\Symfony\Latte\Preprocessor\Preprocessor;

/**
 * # Field Component Preprocessor
 *
 * * Matches `<field:component ... >` tags in the {@see $content} string.
 * * Processes and replaces the matched components in the {@see $content} string.
 */
final class LatteComponentPreprocessor extends Preprocessor
{

    private const ELEMENNTS = [
        'button' => Components\Button::class,
        'icon'   => Asset\Icon::class,
    ];

    private const COMPONENTS = [
        'field:password' => Input\Password::class,
        'field:email' => Input\Email::class,

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

    public function __construct( private readonly CoreDependencies $get ) {}

    public function process() : self {

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
                                  '$object'   => $name,
                                  'component' => $class,
                              ],
                );
                continue;
            }

            foreach ( $components as $data ) {
                $component = new ( $class )( $data, $this->get );
                $this->updateContent( $component->data( 'source', true ), $component->print( true ) );
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

        if ( $count === 0 ) {
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
                matches : $components,
                flags   : PREG_SET_ORDER,
            );

            if ( !$count ) {
                return;
            }

            foreach ( $components as $component ) {
                $source  = $component[ 0 ];
                $element = new( $parser )( ... Html::extractAttributes( $source ) );

                if ( !$element instanceof Printable ) {
                    $this->logger->error(
                        message : "Element does not implement {class}. This is unexpected behaviour, please check the template file. Skipping.",
                        context : [
                                      'source'  => $source,
                                      'element' => $element,
                                      'class'   => Printable::class,
                                  ],
                    );
                    continue;
                }

                if ( isset( $element->tag ) ) {
                    $element->tag->isSelfClosing = str_ends_with( $source, '/>' );
                }

                $this->updateContent( $source, $element->print() );
            }
        }
    }

    private function getComponentNamespace( string $string ) : string {
        if ( str_contains( $string, ' ' ) ) {
            $string = explode( ' ', $string, 2 )[ 0 ];
        }

        return trim( $string );
    }
}