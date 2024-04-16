<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\Security\ErrorEventException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait CoreControllerTrait
{


    /**
     * Return a {@see Response}`view` from a `.latte` template.
     *
     * @param string        $template
     * @param object|array  $parameters
     * @param int           $status
     *
     * @return Response
     */
    protected function response(
        string         $template,
        object | array $parameters = [],
        int            $status = Response::HTTP_OK,
    ) : Response {

        if ( is_array( $parameters ) && isset( $this->document ) ) {
            $parameters[ 'document' ] = $this->document;
        }

        return new Response(
            content : $this->render( $template, $parameters ),
            status  : $status,
        );
    }

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

        if ( !property_exists( $this, 'latte' ) ) {
            return new NotFoundHttpException(
                'Template "latte" does not exist.',

            );
        }

        return $this->latte->render(
            template   : $template,
            parameters : $parameters,
        );
    }

    protected function error404(
        ?string $message = null,
        ?string $content = null,
        string  $template = 'error.latte',
        array   $parameters = [],
        array   $headers = [],
        int     $status = Response::HTTP_NOT_FOUND,
    ) : void {
        throw new ErrorEventException(
            $message ?? 'Page not found.',
            $status,

        );
    }

}