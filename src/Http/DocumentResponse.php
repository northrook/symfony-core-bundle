<?php

namespace Northrook\Symfony\Core\Http;

use Northrook\Env;
use Northrook\Latte;
use Northrook\Logger\Log;
use Northrook\Symfony\Core\DependencyInjection\ServiceContainer;
use Northrook\Symfony\Service\Document\DocumentService;
use Symfony\Component\HttpFoundation\Response;


/**
 */
final class DocumentResponse extends Response
{
    public readonly DocumentService $document;
    public readonly object | array  $context;

    public function __construct(
            string           $content,
            object | array   $context = [],
            int              $status = Response::HTTP_OK,
            array            $headers = [],
            ?DocumentService $document = null,
    )
    {
        $this->document = $document ?? ServiceContainer::get( DocumentService::class );
        $this->context  = $context;
        parent::__construct( $content, $status, $headers );
    }

    public function getContent() : string
    {
        if ( !$this->document->isPublic ) {
            $this->headers->set( 'X-Robots-Tag', 'noindex, nofollow', true );
        }
        return $this->responseContent();
    }

    private function responseContent() : string
    {
        if ( \str_ends_with( $this->content, '.latte' ) ) {
            $latte = ServiceContainer::get( Latte::class );

            if ( !Env::isProduction() ) {
                $latte->clearTemplateCache();
            }
            else {
                Log::critical(
                        'Do not perform {method} on every Latte render in production.',
                        [ 'method' => '$latte->clearTemplateCache()', ],
                );
            }

            return $latte->render(
                    template   : $this->content,
                    parameters : [ 'document' => $this->document ] + $this->context,
            );
        }

        return $this;
    }

    private function responseStatus( int $assume ) : int
    {
        return Response::HTTP_OK;
    }

}