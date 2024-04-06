<?php

namespace Northrook\Symfony\Core\Latte;

use Northrook\Support\Regex;
use Northrook\Symfony\Core\Components\Button;
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
        'button' => Button::class,
    ];

    /**
     * A list of all matched components in the {@see $content} string.
     */
    private array $components = [];

    public function __construct() {}

    public function addComponent(
        string  ...$component
    ) : self {
        foreach ( $component as $object ) {

            if ( !is_subclass_of( $object, Component::class ) ) {
                $this->logger->error(
                    message : "{object} is not a subclass of {component}.",
                    context : [
                                  '$object'   => $object,
                                  'component' => Component::class,
                              ],
                );
                continue;
            }

            if ( in_array( $object, $this->components, true ) ) {
                continue;
            }
            $this->components[] = $object;
        }


        return $this;
    }

    public function process() : self {
        $this->prepareContent( false );
        // $this->components = $this->matchElements();
        // $this->matchComponents();
        // $this->processButtons();
        // $this->processIcons();

        foreach ( $this->matchElements() as $component ) {
            // $this->updateContent( $component->templateString, $component );
            dump( $component );
        }

        dd(
        // $this->components,
            $this->content,
        );
        return $this;
    }

    protected function matchElements() : array {

        $array = [];

        $count = preg_match_all(
                    "/<(?<component>(\w*?):.*?)>/ms",
                    $this->content,
                    $matches,
            flags : PREG_SET_ORDER,
        );

        if ( !$count ) {
            return [];
        }

        foreach ( $matches as $matched ) {
            $element                        = new \Northrook\Symfony\Core\Latte\ProcessComponent( $matched );
            $array[ $element->component ][] = $element;
        }

        return $array;
    }

    private function getComponentNamespace( string $string ) : string {
        if ( str_contains( $string, ' ' ) ) {
            return trim( explode( ' ', $string, 2 )[ 0 ] );
        }

        return $string;
    }

    /**
     * Match components
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

        // dd( $components );

        foreach ( $components as $match ) {

            $element            = $match;
            $this->components[] = $element;

//			$this->components[] = $node;
        }
        dump( $this->components );
    }


}