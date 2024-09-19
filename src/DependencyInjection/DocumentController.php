<?php

namespace Northrook\Symfony\Core\DependencyInjection;

use Northrook\Symfony\Core\Http\DocumentResponse;
use Symfony\Component\HttpFoundation\Response;
use function Northrook\normalizePath;


/**
 * @property bool $isPublic
 */
abstract class DocumentController extends CoreController
{

    /**
     * Return a {@see DocumentResponse} `view` from a `.latte` template.
     *
     * @param string        $template
     * @param object|array  $parameters
     * @param int           $status
     *
     * @return DocumentResponse
     */
    final protected function documentResponse(
            string         $template,
            object | array $parameters = [],
            int            $status = Response::HTTP_OK,
    ) : DocumentResponse
    {
        return new DocumentResponse(
                content  : $this->templatePath( $template ),
                document : $this->document,
                context  : $parameters = [],
        );
    }

    final protected function dynamicTemplatePath( ?string $dir = null ) : string
    {
        $dir  ??= \defined( static::class . '::DYNAMIC_TEMPLATE_DIR' )
                ? static::DYNAMIC_TEMPLATE_DIR : '';
        $file = \str_replace( '/', '.', $this->request->route ) . '.latte';

        return normalizePath( $dir . DIRECTORY_SEPARATOR . $file );
    }

    final public function __get( string $property )
    {
        return match ( $property ) {
            'isPublic' => $this->document->isPublic,
            default    => parent::__get( $property ),
        };
    }

    final public function __set( string $property, mixed $value )
    {
        return match ( $property ) {
            'isPublic' => $this->document->isPublic = $value,
        };
    }

    /**
     * Check if the property exists.
     *
     * @param string  $property
     *
     * @return bool
     */
    public function __isset( string $property ) : bool
    {
        return match ( $property ) {
            'isPublic' => isset( $this->document->isPublic ),
            default    => isset( $this->{$property} ),
        };
    }
}