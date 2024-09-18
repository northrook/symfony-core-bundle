<?php

namespace Northrook\Symfony\Core\Http;

use Northrook\Env;
use Northrook\Latte;
use Northrook\Logger\Log;
use Northrook\Symfony\Core\DependencyInjection\ServiceContainer;
use Northrook\Symfony\Service\Document\DocumentService;
use Symfony\Component\HttpFoundation\Response;


final class DocumentResponse extends Response
{

    public readonly DocumentService $document;

    public function __construct(
            string          $content,
            DocumentService $document,
            int             $status = 200,
            array           $headers = [],
    )
    {
        $this->document = $document;
        parent::__construct(
                content : $this->responseContent( $content ),
                status  : $this->responseStatus( $status ),
                headers : $this->responseHeaders( $headers ),
        );
    }

    private function responseContent( string $string ) : string
    {
        if ( \str_ends_with( $string, '.latte' ) ) {
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

            return $latte->render( $string );
        }

        return $string;
    }

    private function responseStatus( int $assume ) : int
    {
        return Response::HTTP_OK;
    }

    private function responseHeaders( array $headers ) : array
    {
        if ( !$this->document->isPublic ) {
            $headers[ 'X-Robots-Tag' ] = 'noindex, nofollow';
        }
        return $headers;
    }

}