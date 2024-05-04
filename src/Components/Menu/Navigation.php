<?php

namespace Northrook\Symfony\Core\Components\Menu;

use Northrook\Elements\Element;
use Northrook\Support\Str;

class Navigation implements \Stringable
{

    private array          $items = [];
    public readonly string $id;

    public function __construct(
        public readonly string  $root,
        public readonly ?string $current = null,
        ?string                 $id = null,
    ) {
        $this->id = Str::key( $id ?? "$root-navigation", '-' );
    }

    final public function render(
        string | Element\Tag $tag = 'ol',
                             ...$set
    ) : Element {

        $set[ 'tag' ]   = $tag;
        $set[ 'class' ] = 'navigation ' . ( $set[ 'class' ] ?? '' );

        $menu = new Element( ... $set );

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
                $menu->setNavigation( $this );
                $this->items[ $id ] = $menu->href( root : $this->root );
            }

        }

        else {
            $this->items[ $item->id ?? $key ?? (string) count( $this->items ) ] = $item;
        }

        return $this;
    }
}