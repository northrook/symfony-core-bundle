<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\ResponseHandler;

use Northrook\Symfony\Core\DependencyInjection\ServiceContainer;
use Northrook\Symfony\Service\Document\DocumentService;
use Northrook\Trait\PropertyAccessor;
use Symfony\Component\HttpFoundation\Response;


/**
 * @property-read DocumentService $document
 *
 * @internal
 */
final class RenderPayload extends Response
{
    use PropertyAccessor;


    /**
     * @param string        $content
     * @param object|array  $context
     */
    public function __construct(
            string                          $content,
            private readonly array | object $context = [],
            int                             $status = Response::HTTP_OK,
            array                           $headers = [],
    )
    {
        parent::__construct( $content, $status, $headers );
    }

    public function __get( string $property )
    {
        return match ( $property ) {
            'document' => ServiceContainer::get( DocumentService::class ),
            default    => null,
        };
    }

}