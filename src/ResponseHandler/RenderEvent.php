<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\ResponseHandler;

use Northrook\Get;
use Northrook\Settings;
use Northrook\Symfony\Core\DependencyInjection\ServiceContainer;
use Northrook\Symfony\Core\Service\CurrentRequest;
use Northrook\Symfony\Service\Document\DocumentService;
use Northrook\Symfony\Service\Toasts\Message;
use Northrook\UI\AssetHandler;
use Northrook\UI\Component\Notification;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use function Northrook\toString;
use const Northrook\EMPTY_STRING;


/**
 * Handles both `kernel.response` and `kernel.view` events.
 *
 * @author  Martin Nielsen <mn@northrook.com>
 */
final readonly class RenderEvent implements EventSubscriberInterface
{

    public function __construct(
            private CurrentRequest $request,
    ) {}

    public function kernelRequestEvent()
    {
        dump(
                ServiceContainer::get( ServiceLocator::class ),
        );
    }

    public function kernelResponseEvent(
            ResponseEvent            $response,
            string                   $event,
            EventDispatcherInterface $dispatcher,
    ) : void
    {
        if ( $this->ignoreEvent() ) {
            return;
        }
        dump(
                $response->getResponse(),
                $this->request,
        );
        // dump( __METHOD__, $response, $event, $dispatcher, $this );
    }

    public function kernelViewEvent( ...$args ) : Response
    {
        dump( 'kernelViewEvent', $this->request, $args );
        return new Response(
                'intercepted',
        );
    }

    public static function getSubscribedEvents() : array
    {
        return [
                'kernel.request'  => 'kernelRequestEvent',
                'kernel.response' => 'kernelResponseEvent',
                // 'kernel.view'     => 'kernelViewEvent',
        ];
    }

    private function responseContent( ?string $content ) : string
    {
        $notifications = $this->handleFlashBag();
        $runtimeAssets = new AssetHandler( Get::path( 'dir.assets' ) );
        if ( \property_exists( $this, 'document' )
             &&
             $this->document instanceof DocumentService ) {
            $this->document->asset( $runtimeAssets->getComponentAssets() );
            return $this->document->renderDocumentHtml( $content, $notifications );
        }

        return $notifications . $content;
    }

    private function injectAssets( array $assets ) : void {}

    private function handleFlashBag() : string
    {
        $notifications = EMPTY_STRING;

        foreach ( $this->request->flashBag()->all() as $type => $flash ) {
            foreach ( $flash as $toast ) {
                $notification = $this->resolveFlash( $toast, $type );

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

    private function resolveFlash( mixed $toast, int | string $type ) : Notification
    {
        if ( $toast instanceof Message ) {
            $notification = new Notification(
                    $toast->type,
                    $toast->message,
                    $toast->description,
                    $toast->timeout,
            );
        }
        return new Notification( $type, toString( $toast ) );
    }

    private function ignoreEvent() : bool
    {
        if ( \str_starts_with( $this->request->controller, 'web_profiler' ) ) {
            return true;
        }
        return false;
    }
}