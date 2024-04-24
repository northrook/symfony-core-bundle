<?php

namespace Northrook\Symfony\Core\Components\Menu;

use Doctrine\Bundle\DoctrineBundle\Tests\Twig\DummyClass;
use Northrook\Elements\Element;
use Northrook\Elements\Icon;
use Northrook\Support\Get;
use Northrook\Support\Str;
use Northrook\Types\Url;

class Item extends Element implements MenuItemInterface
{

    public const TAG = 'li';

    protected ?string $icon     = null;
    protected ?string $href     = null;
    public bool       $render   = true;
    protected array   $children = [];

    public function __construct( ...$set ) {
        parent::__construct( ...$set );
        $this->render = $this->joink( 'render' ) ?? true;

        $this->class->add( 'menu-item' );
    }

    protected function onPrint() : void {

        if ( $this->href ) {
            $item = "<a href=\"{$this->href}\">{$this->icon()}{$this->label()}</a>";
        }
        else {
            $item = "{$this->icon()}{$this->label()}";
        }

        $this->content = [ 'item' => $item ];

        if ( !empty( $this->children ) ) {
            $children = '';
            foreach ( $this->children as $child ) {
                $child->href( root : $this->href );
                $children .= (string) $child;
            }


            // $children = Element\Content::render( $children);

            $this->content[ 'children' ] = '<ul>' . $children . '</ul>';
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

        $this->href = '/' . trim( $href, " \n\r\t\v\0/" );

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

    private function label() : ?string {
        $label = $this->joink( 'label' );
        return $label ? "<span class=\"label\">$label</span>" : null;
    }

    private function icon() : ?string {
        $icon = $this->joink( 'icon' );
        return $icon ? Icon::svg( $icon ) : null;
    }

    private function joink( string $key ) : mixed {
        $attribute = $this->attributes[ $key ] ?? null;
        unset( $this->attributes[ $key ] );
        return $attribute;
    }
}