<?php

namespace Northrook\Symfony\Core\Components\Menu;

use Northrook\Elements\Element;

interface MenuItemInterface
{
    public function add( array | Element $child ) : self;

    public function hasChildren() : bool;

    public function setNavigation( Navigation $navigation ) : self;
}