<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\DependencyInjection\CoreDependencies;use Northrook\Symfony\Core\DependencyInjection\Trait\CorePropertiesPromoter;use Northrook\Symfony\Core\DependencyInjection\Trait\LatteRenderer;use Northrook\Symfony\Core\DependencyInjection\Trait\NotificationServices;use Northrook\Symfony\Core\DependencyInjection\Trait\ResponseMethods;use Northrook\Symfony\Core\DependencyInjection\Trait\SecurityServices;use Symfony\Component\HttpFoundation\Response;

final class PublicController
{

    use ResponseMethods, LatteRenderer, NotificationServices, SecurityServices, CorePropertiesPromoter;

    public const STYLESHEETS          = [ 'dir.core.assets/styles' ];
    public const DYNAMIC_TEMPLATE_DIR = 'public';

    public function __construct(
        protected readonly CoreDependencies $get,
    ) {
        if ( false === $this->request->is( 'hypermedia' ) ) {
            $this->stylesheet->includeStylesheets( $this::STYLESHEETS )->save( force : true );
        }

        $this->document->stylesheet( 'dir.cache/styles/styles.css' );

        $this->document->script( 'dir.assets/scripts/core.js' );

        $this->document->body();
    }

    public function index(
        ?string $route,
    ) : Response {
        return $this->response(
            template   : 'public.latte',
            parameters : [ 'route' => $route ],
        );
    }

}