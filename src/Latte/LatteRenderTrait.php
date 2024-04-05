<?php

namespace Northrook\Symfony\Core\Latte;

use Northrook\Elements\Element;
use Northrook\Symfony\Latte\Core;
use Symfony\Component\HttpFoundation\Response;

/**
 * * Integrates {@see Core\Environment} from `northrook/symfony-latte-bundle`
 *
 * @property ?Core\Environment $latte
 *
 * @version 0.1.0 ☑️
 * @author  Martin Nielsen <mn@northrook.com>
 */
trait LatteRenderTrait
{

    /**
     * Run at the very start of the {@see Core\Environment} render chain.
     *
     * @return void
     */
    protected function onLatteRender() : void {}

    /**
     * @param string             $view  Template file or template string
     * @param object|array|null  $parameters
     *
     * @return string
     */
    protected function latte(
        string                $view,
        object | array | null $parameters = null,
    ) : string {

        $this->onLatteRender();

        $parameters ??= [];

        if ( is_array( $parameters ) && isset( $this->document ) ) {
            $parameters[ 'document' ] = $this->document;
        }


        return $this->latte->render(
            template   : $view,
            parameters : $parameters,
        );
    }

    /**
     */
    protected function modalResponse(
        string         $view,
        object | array $parameters = [],
        int            $status = Response::HTTP_OK,
        array          $headers = [],
        array          $attributes = [],
        // UI\Button $button -  from Latte Components
    ) : Response {

        $options = [
            'Template-Type' => 'modal', // TODO: [?] as Enum from Latte Components
        ];

        $content = $this->latte( $view, $parameters );

        $modal = ( string) new Element(
            tag        : 'modal',
            attributes : $attributes,
            content    : "<section class='modal-content'>$content</section>", // if array is passed, simple implode
        );

        return new Response(
            content : $modal,
            status  : $status,
            headers : $options + $headers,
        );
    }


    /**
     * @param string        $view
     * @param object|array  $parameters
     * @param int           $status
     * @param array         $headers
     *
     * @return Response
     */
    protected function latteResponse(
        string         $view,
        object | array $parameters = [],
        int            $status = Response::HTTP_OK,
        array          $headers = [],
    ) : Response {
        return new Response(
            content : $this->latte( $view, $parameters ),
            status  : $status,
            headers : $headers,
        );
    }
}