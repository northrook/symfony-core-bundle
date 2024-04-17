<?php

namespace Northrook\Symfony\Core\EventListener;

use Northrook\Elements\Render\Template;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\SecurityService;
use Northrook\Symfony\Core\Services\SettingsManagementService;
use Northrook\Symfony\Latte\Core\Environment;
use Northrook\Symfony\Latte\Parameters\Document;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final readonly class ExceptionListener
{

    public function __construct(
        public SecurityService           $security,
        public CurrentRequestService     $request,
        public SettingsManagementService $settings,
        public Environment               $latte,
        public Document                  $document,
        public ?LoggerInterface          $logger,
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

        return $this->latte->render(
            template   : $template,
            parameters : array_merge( [ 'document' => $this->document ], $parameters ),
        );
    }

    public function __invoke( ExceptionEvent $event ) : void {

        $exception = $event->getThrowable();

        if ( $exception instanceof HttpExceptionInterface ) {
            
            $this->document->addStylesheet( 'dir.cache/styles/styles.css' );
            $this->document->addScript(
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