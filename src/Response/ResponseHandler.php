<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Response;

use Northrook\Symfony\Core\Telemetry\Clerk;
use Northrook\Symfony\Service\Document\DocumentService;


/**
 * @phpstan-return HtmlResponse
 */
final class ResponseHandler
{
    private ?DocumentService      $documentService;
    private null | string         $content    = null;
    private null | array | object $parameters = null;

    /**
     * @param \Closure<DocumentService>  $lazyDocumentService
     */
    public function __construct( private readonly \Closure $lazyDocumentService )
    {
        Clerk::event( $this::class, 'controller' );
    }

    public function __invoke() : HtmlResponse
    {
        Clerk::stopGroup( 'controller' );
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

    public function document( ?bool $isPublic = null ) : DocumentService
    {
        $this->getDocumentService();

        if ( $isPublic !== null ) {
            $this->documentService->isPublic = $isPublic;
        }

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