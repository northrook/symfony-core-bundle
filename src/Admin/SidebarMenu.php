<?php

namespace Northrook\Symfony\Core\Admin;

use Northrook\Symfony\Core\Service\CurrentRequest;
use Northrook\UI\Model\Menu;


final readonly class SidebarMenu implements \Stringable
{
    public Menu $menu;

    public function __construct(
            private CurrentRequest $request,
    )
    {
        $this->menu = new Menu( 'sidebar', $this->request->routeRoot );

        $this->configureSidebarMenu();
    }

    private function configureSidebarMenu() : void
    {
        $this->menu->items(
                Menu::link( title : 'Dashboard', href : '/dashboard', icon : 'ui:dashboard' )
                    ->submenu(
                            Menu::link( title : 'Content', href : '/content', icon : 'ui:layers' ),
                            Menu::link( title : 'Analytics', href : '/analytics', icon : 'ui:bar-chart' ),
                    )
                //  ->actions( Element ... $action ) or custom Action class
                ,
                Menu::link( title : 'Website', href : '/content', icon : 'ui:hex-bolt' )
                    ->submenu(
                            Menu::link( title : 'Pages', href : '/pages' ),
                            Menu::link( title : 'Products', href : '/products' ),
                            Menu::link( title : 'Services', href : '/services' ),
                            Menu::link( title : 'Articles', href : '/articles' ),
                            Menu::link( title : 'Taxonomies', href : '/taxonomies' ),
                    ),
                Menu::item( title : 'Settings', href : 'settings', icon : 'ui:settings' )
                    ->submenu(
                            Menu::link( title : 'General', href : '/settings' ),
                            Menu::link( title : 'Appearance', href : '/appearance' ),
                            Menu::link( title : 'Accounts', href : '/accounts' ),
                            Menu::link( title : 'Performance', href : '/performance' ),
                    ),
                Menu::link(
                        title      : 'User',
                        href       : './admin/user/profile/',
                        icon       : 'user',
                        attributes : [ 'class' => 'mt-auto' ],
                ),
        );
    }

    public function __toString() : string
    {
        return $this->menu->render();
    }
}