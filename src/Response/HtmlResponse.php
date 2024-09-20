<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Response;

use Northrook\Get;
use Northrook\Latte;
use Northrook\Settings;
use Northrook\Symfony\Core\DependencyInjection\ServiceContainer;
use Northrook\Symfony\Core\Service\CurrentRequest;
use Northrook\Symfony\Core\Telemetry\Clerk;
use Northrook\Symfony\Service\Document\DocumentService;
use Northrook\Symfony\Service\Toasts\Message;
use Northrook\UI\AssetHandler;
use Northrook\UI\Component\Notification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function Northrook\toString;
use const Northrook\EMPTY_STRING;


final class HtmlResponse extends Response
{

    private bool         $isRendered = false;
    public readonly bool $isTemplate;

    /**
     * @param string                $content
     * @param null | object|array   $parameters
     * @param null|DocumentService  $documentService
     * @param int                   $status
     * @param array                 $headers
     */
    public function __construct(
            string                            $content,
            private null | array | object     $parameters = [],
            private readonly ?DocumentService $documentService = null,
            int                               $status = Response::HTTP_OK,
            array                             $headers = [],
    )
    {
        Clerk::event( 'HtmlResponse', 'response' );
        $this->isTemplate = $this->parameters !== null;
        parent::__construct( $content, $status, $headers );
    }

    public function prepare( Request $request ) : static
    {
        $this->render();
        Clerk::event( 'HtmlResponse' )->stop();
        return parent::prepare( $request );
    }

    public function isRendered() : bool
    {
        return $this->isRendered;
    }

    public function render() : void
    {
        if ( $this->isRendered || $this->isTemplate === false ) {
            return;
        }

        $this->content = $this->renderContent();

        $notifications = $this->flashBagHandler();

        $this->assetHandler();

        if ( $this->documentService ) {
            $this->content = $this->documentService->renderDocumentHtml(
                    $this->content, $notifications,
            );
        }
        else {
            $this->content = $notifications . $this->content;
        }

        $this->isRendered = true;
    }

    private function renderContent() : string
    {
        $latte = $this->latteEnvironment();

        if ( !$this->documentService ) {
            return $latte->render(
                    template   : $this->content,
                    parameters : $this->parameters,
            );
        }

        $layout = \strstr( $this->content, '/', true );

        $this->documentService->document->add( 'body.id', $layout );

        $this->parameters[ 'template' ] = $this->content;
        $this->parameters[ 'document' ] = $this->documentService;

        return $latte->render(
                template   : "$layout.latte",
                parameters : $this->parameters,
        );
    }

    private function flashBagHandler() : string
    {
        $flashes       = $this->request()->flashBag()->all();
        $notifications = EMPTY_STRING;

        foreach ( $flashes as $type => $flash ) {
            foreach ( $flash as $toast ) {
                $notification = match ( $toast instanceof Message ) {
                    true  => new Notification(
                            $toast->type,
                            $toast->message,
                            $toast->description,
                            $toast->timeout,
                    ),
                    false => new Notification(
                            $type,
                            toString( $toast ),
                    ),
                };

                if ( !$notification->description ) {
                    $notification->attributes->add( 'class', 'compact' );
                }

                if ( !$notification->timeout && $notification->type !== 'danger' ) {
                    $notification->setTimeout( Settings::get( 'notification.timeout' ) ?? 5000 );
                }

                $notifications .= $notification;
            }
        }

        return $notifications;
    }

    private function assetHandler() : void
    {
        $runtimeAssets = ( new AssetHandler( Get::path( 'dir.assets' ) ) )->getComponentAssets();

        if ( $this->documentService ) {
            $this->documentService->asset( $runtimeAssets );
            return;
        }

        if ( $this->request()->isHtmx ) {
            $assets = EMPTY_STRING;

            foreach ( $runtimeAssets as $asset ) {
                $assets .= $asset->getInlineHtml( true );
            }

            $this->content = $assets . $this->content;
        }
    }

    private function latteEnvironment() : Latte
    {
        $latte = ServiceContainer::get( Latte::class );

        // if ( !Env::isProduction() ) {
        // $latte->clearTemplateCache();
        // }
        // else {
        //     Log::critical(
        //             'Do not perform {method} on every Latte render in production.',
        //             [ 'method' => '$latte->clearTemplateCache()', ],
        //     );
        // }

        return $latte;
    }

    private function request() : CurrentRequest
    {
        return ServiceContainer::get( CurrentRequest::class );
    }

}