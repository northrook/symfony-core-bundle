<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\DependencyInjection\CoreDependencies;
use Northrook\Symfony\Core\DependencyInjection\Trait\CorePropertiesPromoter;
use Northrook\Symfony\Core\DependencyInjection\Trait\LatteRenderer;
use Northrook\Symfony\Core\DependencyInjection\Trait\NotificationServices;
use Northrook\Symfony\Core\DependencyInjection\Trait\ResponseMethods;
use Northrook\Symfony\Core\DependencyInjection\Trait\SecurityServices;
use Symfony\Component\HttpFoundation\Response;

final class PublicController
{

    use ResponseMethods, LatteRenderer, NotificationServices, SecurityServices, CorePropertiesPromoter;

    public const STYLESHEETS          = [ 'dir.core.assets/styles' ];
    public const DYNAMIC_TEMPLATE_DIR = 'public';

    public function __construct(
        protected readonly CoreDependencies $get,
    ) {

        $this->stylesheet->includeStylesheets( $this::STYLESHEETS )->save( force : true );

        $this->document->stylesheet( 'dir.cache/styles/styles.css' );

        $this->document->script( 'dir.assets/scripts/core.js' );

        $this->document->body();
    }

    public function index(
        ?string $route,
    ) : Response {
        //
        // if ( time() % 2 === 0 ) {
        //     $this->addFlash( 'info', 'The `time()` is even.', 'Recorded timestamp: `' . time() . '`,' );
        // }
        // $this->addFlash( 'info', 'Info Message', __METHOD__ );
        // $this->addFlash( 'info', 'Info Message' );
        // $this->addFlash( 'error', 'An error occurred!', __METHOD__, );
        // $this->addFlash( 'info', 'Info Message', __METHOD__ );
        // $this->addFlash( 'warning', 'Warning!', 'Nuclear launch detected.' );
        //
        // $this->addFlash( 'error', 'This is an error title', );
        // $this->addFlash( 'info', 'Info Message', __METHOD__ );
        // $this->addFlash( 'notice', 'Notice me', 'Non-hinted type.' );
        //
        //
        // // sleep(1);
        // $this->addFlash( 'info', 'Info Message', __METHOD__ );


        return $this->response(
            template   : 'public.latte',
            parameters : [
                             'route' => $route,
                         ],
        );
    }

}