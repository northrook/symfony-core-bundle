<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\EventSubscriber;

use Northrook\Symfony\Core\App;
use Northrook\Symfony\Core\DependencyInjection\ServiceContainer;
use Northrook\Symfony\Core\Service\CurrentRequest;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;


/**
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
                ServiceContainer::get(),
                App::serviceContainer( ServiceLocator::class ),
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

    private function ignoreEvent() : bool
    {
        if ( \str_starts_with( $this->request->controller, 'web_profiler' ) ) {
            return true;
        }
        return false;
    }
}