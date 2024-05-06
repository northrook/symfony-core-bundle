<?php

namespace Northrook\Symfony\Core\EventListener;

use Northrook\Elements\Render\Template;
use Northrook\Symfony\Core\DependencyInjection\CoreDependencies;
use Northrook\Symfony\Core\DependencyInjection\Trait\CorePropertiesPromoter;
use Northrook\Symfony\Core\DependencyInjection\Trait\ResponseMethods;
use Northrook\Symfony\Core\Services\DocumentService;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * @property DocumentService $document
 */
final  class ExceptionListener
{
    use CorePropertiesPromoter, ResponseMethods;

    public function __construct(
        protected readonly CoreDependencies $get,
    ) {}

    public function __invoke( ExceptionEvent $event ) : void {

        $exception = $event->getThrowable();

        if ( $exception instanceof HttpExceptionInterface ) {

            $this->document->stylesheet( 'dir.cache/styles/styles.css' );
            $this->document->script(
                'dir.assets/scripts/core.js',
                'dir.assets/scripts/components.js',
            );

            $template   = $exception->template ?? 'error.latte';
            $parameters = array_merge(
                [
                    'message' => $exception->getMessage(),
                    'status'  => $exception->getStatusCode(),
                    'content' => $exception->content ?? null,
                ], $exception->parameters ?? [],
            );

            if ( $parameters[ 'content' ] instanceof Template ) {
                $template = $parameters[ 'content' ];
                unset( $parameters[ 'content' ] );
                $parameters[ 'content' ] = $template->addData( $parameters );
            }

            $event->setResponse(
                $this->response(
                    $template,
                    $parameters,
                    $exception->getStatusCode(),
                ),
            );
        }
    }
}