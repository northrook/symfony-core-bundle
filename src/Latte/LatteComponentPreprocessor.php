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
        $this->prepareContent( true );
        $this->matchComponents();
        // $this->processButtons();
        // $this->processIcons();

        // foreach ( $this->components as $component ) {
        //     $this->updateContent( $component->templateString, $component );
        // }

        return $this;
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
        dd( $this->components );
    }


}