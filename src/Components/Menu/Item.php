<?php

namespace Northrook\Symfony\Core\Components\Menu;

use Doctrine\Bundle\DoctrineBundle\Tests\Twig\DummyClass;
use Northrook\Elements\Asset\Icon;
use Northrook\Elements\Asset\SVG;
use Northrook\Elements\Button;
use Northrook\Elements\Element;
use Northrook\Support\Get;
use Northrook\Support\Str;
use Northrook\Types\Url;

class Item extends Element implements MenuItemInterface
{

    public const TAG     = 'li';
    public const CLASSES = 'menu-item';

    private Navigation $navigation;

    // private string        $id;
    protected string      $label;
    protected Icon | null $icon     = null;
    protected ?string     $href     = null;
    public bool           $render   = true;
    protected Element     $item;
    protected array       $children = [];

    public function __construct( ...$set ) {

        $this->render = $set[ 'render' ] ?? true;
        unset( $set[ 'render' ] );

        if ( !$this->render ) {
            return;
        }
        // Set the Icon if provided
        $this->label = $set[ 'label' ] ?? null;
        unset( $set[ 'label' ] );


        // Set the Icon if provided
        $this->icon = ( $set[ 'icon' ] ?? null ) ? new Icon( $set[ 'icon' ] ) : null;
        unset( $set[ 'icon' ] );

        $this->item = new Element( class : 'item' );


        parent::__construct( ...$set );


        // $this->class->add( 'menu-item' );
    }

    final public function setNavigation( Navigation $navigation ) : self {
        if ( !isset( $this->navigation ) ) {
            $this->navigation = $navigation;
        }

        $this->id = Str::key( $this->navigation->id . '-' . $this->label, '-' );

        return $this;
    }

    protected function onPrint() : void {

        if ( $this->icon ) {
            $this->item->content( [ 'icon' => $this->icon->print() ] );
        }

        if ( $this->href ) {

            $anchor = "<a href=\"/$this->href\" ";
            if ( $this->href === $this->navigation->current ) {
                $anchor .= "aria-current=\"page\" class=\"active\"";
            }
            $anchor .= ">{$this->label()}</a>";
            $this->item->content( [ 'anchor' => $anchor ] );
        }
        else {
            $this->item->content( [ 'label' => $this->label() ] );
        }

        // dump( $this->item );
        $this->content[ 'item' ] = $this->item;

        if ( !empty( $this->children ) ) {

            $this->class->add( 'has-children' );

            $this->item->content(
                [
                    'toggle' => new Button(
                        class         : 'submenu-toggle icon ',
                        label         : 'Toggle Sub Menu',
                        aria_controls : $this->id,
                        aria_expanded : false,
                        content       : Icon::chevron( 'toggle' ),
                    ),
                ],
            );

            $children = '';
            foreach ( $this->children as $child ) {

                if ( $child instanceof MenuItemInterface ) {
                    $child->setNavigation( $this->navigation );
                }

                $child->href( root : $this->href );
                $children .= (string) $child;
            }


            // $children = Element\Content::render( $children);

            $this->content[ 'children' ] = '<ol id="' . $this->id . '-submenu" class="sub-menu">' . $children . '</ol>';
        }

    }

    public function href( ?string $link = null, ?string $root = null ) : self {

        $href = $this->joink( 'link' );

        if ( !$href ) {
            return $this;
        }

        if ( $root ) {
            $href = "/$root/$href";
        }

        $this->href = trim( $href, " \n\r\t\v\0/" );

        return $this;
    }

    public function add( array | Element $child, ?string $key = null ) : self {

        if ( is_array( $child ) ) {
            $this->children = array_merge( $this->children, $child );
        }
        else {
            $this->children[ $child->id ?? $key ?? (string) count( $this->children ) ] = $child;
        }

        return $this;
    }

    public function hasChildren() : bool {
        return count( $this->children ) > 0;
    }


    private function label() : string {
        return "<span class=\"label\">$this->label</span>";
    }

    private function joink( string $key ) : mixed {
        $attribute = $this->attributes[ $key ] ?? null;
        unset( $this->attributes[ $key ] );
        return $attribute;
    }
}