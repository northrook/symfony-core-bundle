<?php

declare(strict_types=1);

namespace Northrook\Symfony\Core\Response;

use Northrook\{Clerk, Get, Latte, Settings};
use Northrook\Symfony\Core\DependencyInjection\ServiceContainer;
use Northrook\Symfony\Core\Service\CurrentRequest;
use Northrook\Symfony\Service\DocumentService;
use Northrook\Symfony\Service\Toasts\Message;
use Northrook\UI\Component\Notification;
use Symfony\Component\HttpFoundation\{Request, Response};
use function Support\toString;
use const Support\EMPTY_STRING;

/**
 * @internal
 * @author Martin Nielsen <mn@northrook.com>
 */
final class HtmlResponse extends Response
{
    private bool $isRendered = false;

    public readonly bool $isTemplate;

    /**
     * @param string            $content
     * @param null|array|object $parameters
     * @param ?DocumentService  $documentService
     * @param int               $status
     * @param array             $headers
     */
    public function __construct(
        string                            $content,
        private null|array|object         $parameters = [],
        private readonly ?DocumentService $documentService = null,
        int                               $status = Response::HTTP_OK,
        array                             $headers = [],
    ) {
        Clerk::event( $this::class, 'response' );
        $this->isTemplate = null !== $this->parameters;
        parent::__construct( $content, $status, $headers );
    }

    public function prepare( Request $request ) : static
    {
        $this->render();
        Clerk::event( $this::class )->stop();
        return parent::prepare( $request );
    }

    public function isRendered() : bool
    {
        return $this->isRendered;
    }

    public function render() : void
    {
        if ( $this->isRendered || false === $this->isTemplate ) {
            return;
        }

        $this->content = $this->renderContent();

        $notifications = $this->flashBagHandler();

        $this->assetHandler();

        if ( $this->documentService ) {
            $this->content = $this->documentService->renderDocumentHtml(
                $this->content,
                $notifications,
            );
            Clerk::stopGroup( 'document' );
        }
        else {
            $this->content = $notifications.$this->content;
        }

        $this->isRendered = true;
    }

    private function renderContent() : string
    {
        $latte = $this->latteEnvironment();

        if ( ! $this->documentService ) {
            return $latte->render(
                template   : $this->content,
                parameters : $this->parameters,
            );
        }

        $layout = \strstr( $this->content, '/', true );

        $this->documentService->properties->add( 'body.id', $layout )
            ->add( 'body.data-route', $this->request()->route );

        $this->parameters['template'] = $this->content;
        $this->parameters['document'] = $this->documentService;

        return $latte->render(
            template   : "{$layout}.latte",
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
                    true => new Notification(
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

                if ( ! $notification->description ) {
                    $notification->attributes->add( 'class', 'compact' );
                }

                if ( ! $notification->timeout && 'error' !== $notification->type ) {
                    $notification->setTimeout( Settings::get( 'notification.timeout' ) ?? 5_000 );
                }

                $notifications .= $notification;
            }
        }

        return $notifications;
    }

    private function assetHandler() : void
    {
        $runtimeAssets = ( new \Northrook\UI\AssetHandler( Get::path( 'dir.assets' ) ) )->getComponentAssets();

        if ( $this->documentService ) {
            $this->documentService->asset( $runtimeAssets, minify : $this->assetHandler->minify );
            return;
        }

        if ( $this->request()->isHtmx ) {
            $assets = EMPTY_STRING;

            foreach ( $runtimeAssets as $asset ) {
                $assets .= $asset->getInlineHtml( true );
            }

            $this->content = $assets.$this->content;
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
