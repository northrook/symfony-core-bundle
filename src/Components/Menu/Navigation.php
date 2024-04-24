<?php

namespace Northrook\Symfony\Core\Components\Menu;

use Northrook\Elements\Element;
use Northrook\Support\Str;

class Navigation implements \Stringable
{

    private array $items = [];

    public function __construct(
        private readonly string $root,
    ) {}

    final public function render(
        string | Element\Tag $tag = 'ul',
        ?string              $id = null,
        ?string              $class = "navigation",
                             ...$set
    ) : Element {
        $menu = new Element( ... [ 'tag' => $tag, 'id' => $id, 'class' => $class, ... $set ] );

        foreach ( $this->items as $item ) {
            $menu->content[] = (string) $item;
        }

        return $menu;

    }


    final public function __toString() : string {
        return $this->render()->print( true );
    }

    /**
     * @TODO : Option to insert before/after existing key (or append/prepend if not found)
     *
     * @param Item[]|Element[]  $item
     * @param null|string       $key  Manually assign key when adding an Element, or prefix every item when adding an array.
     *
     * @return $this
     *
     */
    final public function add(
        array | MenuItemInterface | Element $item,
        ?string                             $key = null,
    ) : self {

        if ( $item instanceof MenuItemInterface ) {
            $item = [ $item ];
        }

        if ( is_array( $item ) ) {


            foreach ( $item as $id => $menu ) {
                if ( !$menu->render ) {
                    continue;
                }
                $this->items[ $id ] = $menu->href( root : $this->root );
            }

        }

        else {
            $this->items[ $item->id ?? $key ?? (string) count( $this->items ) ] = $item;
        }

        return $this;
    }
}