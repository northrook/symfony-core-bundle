<?php

namespace Northrook\Symfony\Core\EventListener;

use Northrook\Elements\Render\Template;
use Northrook\Symfony\Core\DependencyInjection\CoreDependencies;
use Northrook\Symfony\Core\DependencyInjection\Trait\PropertiesPromoter;
use Northrook\Symfony\Core\Services\DocumentService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * @property DocumentService $document
 */
final readonly class ExceptionListener
{
    use PropertiesPromoter;

    public function __construct(
        protected CoreDependencies $get,
    ) {}

    /**
     * Render a `.latte` template to string.
     *
     * @param string        $template
     * @param object|array  $parameters
     *
     * @return string
     */
    protected function render(
        string         $template,
        object | array $parameters = [],
    ) : string {

        return $this->get->render(
            template   : $template,
            parameters : array_merge( [ 'document' => $this->document ], $parameters ),
        );
    }

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
                new Response(
                    $this->render( $template, $parameters ),
                    $exception->getStatusCode(),
                ),
            );
        }
    }
}