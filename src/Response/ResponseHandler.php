<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Response;

use Northrook\Symfony\Core\Service\CurrentRequest;
use Northrook\Symfony\Service\Document\DocumentService;


/**
 * @phpstan-return HtmlResponse
 */
final class ResponseHandler
{
    private readonly DocumentService $documentService;
    private null | string            $content    = null;
    private null | array | object    $parameters = null;

    /**
     * @param \Northrook\Symfony\Core\Service\CurrentRequest  $request
     * @param \Closure<DocumentService>                       $lazyDocumentService
     */
    public function __construct(
            private readonly CurrentRequest $request,
            private readonly \Closure       $lazyDocumentService,
    ) {}

    public function __invoke() : HtmlResponse
    {
        return new HtmlResponse(
                $this->content,
                $this->parameters,
                $this->documentService ?? null,
        );
    }

    public function html( string $html, bool $override = false ) : self
    {
        if ( $this->content && !$override ) {
            throw new \LogicException( 'The content has already been set.', );
        }

        $this->content = $html;
        return $this;
    }

    // ::: Templating

    public function template( string $template, array | object $parameters = [], bool $override = false ) : self
    {
        if ( $this->content && !$override ) {
            throw new \LogicException( 'The template has already been set.', );
        }

        $this->content    = $template;
        $this->parameters = $parameters;
        return $this;
    }

    public function addParameter( string $key, $value ) : self
    {
        $this->parameters[ $key ] ??= $value;
        return $this;
    }

    public function setParameter( string $key, $value ) : self
    {
        $this->parameters[ $key ] = $value;
        return $this;
    }

    public function hasParameter( string $key ) : bool
    {
        return \array_key_exists( $key, $this->parameters );
    }

    public function getParameter( string $key ) : mixed
    {
        return $this->parameters[ $key ] ?? null;
    }

    public function document( bool $isPublic = false ) : DocumentService
    {
        $this->getDocumentService()->isPublic = $isPublic;
        return $this->documentService;
    }

    /**
     * Instantiate the DocumentService on-demand.
     *
     * @return DocumentService
     */
    private function getDocumentService() : DocumentService
    {
        return $this->documentService ??= ( $this->lazyDocumentService )();
    }

}