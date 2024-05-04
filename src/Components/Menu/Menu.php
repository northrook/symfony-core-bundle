<?php

namespace Northrook\Symfony\Core\Components\Menu;

use Northrook\Elements\Asset\SVG;

class Menu
{

    /**
     * @param                 $label   // The text label for the item
     * @param                 $icon    // Accepts asset string, Icon::class, raw HTML
     * @param string | false  $link    // Relative path, absolute url, or route
     * @param bool            $render  // Minimum permissions required for render
     *
     * @return Item
     */
    public static function item(
        string $label,
        ?string $icon = null,
        string | false $link = false,
        bool $render = null,
        ...$properties
    ) : Item {
        $get = get_defined_vars();
        unset( $get[ 'properties' ] );
        return new Item( ... array_merge( $get, $properties ) );
    }

    public static function link() : Link {
        return new Link();
    }
}