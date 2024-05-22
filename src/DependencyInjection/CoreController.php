<?php

namespace Northrook\Symfony\Core\DependencyInjection;

use Exception;
use Latte\RuntimeException;
use LogicException;
use Northrook\Symfony\Core\Facade\Logger;
use Northrook\Symfony\Core\Facade\Path;
use Northrook\Symfony\Core\Security\ErrorEventException;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\DocumentService;
use Northrook\Symfony\Core\Services\NotificationService;
use Northrook\Symfony\Latte;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

/**
 * @author  Martin Nielsen <mn@northrook.com>
 *
 * @link    https://github.com/northrook Documentation
 *
 * @property CurrentRequestService $request
 *
 * @internal
 */
abstract class CoreController extends Facade
{

    /**
     * Render a `.latte` template to string.
     *
     * @param string        $template
     * @param object|array  $parameters
     *
     * @return string
     */
    final protected function render(
        string         $template,
        object | array $parameters = [],
    ) : string {

        $latte = static::getService( Latte\Environment::class );

        if ( !$latte ) {
            throw new LogicException( 'Latte is not available.' );
        }

        // If the document is not passed as a parameter, we will try to get it from the controller
        if ( !isset( $parameters[ 'document' ] ) && property_exists( $this, 'document' ) ) {
            $latte->addGlobalVariable(
                'document',
                $this->document instanceof DocumentService
                    ? $this->document->getDocumentVariable()
                    : $this->document,
            );
        }

        try {
            $content = $latte->render(
                template   : $template,
                parameters : $parameters,
            );
        }
        catch ( RuntimeException $e ) {
            throw new ErrorEventException(
                message  : $e->getMessage(),
                previous : $e,
            );
        }

        return static::getService( NotificationService::class )
                     ->injectFlashBagNotifications( $content );
    }


    /**
     * Return a {@see Response}`view` from a `.latte` template.
     *
     * @param string        $template
     * @param object|array  $parameters
     * @param int           $status
     *
     * @return Response
     */
    final protected function response(
        string         $template,
        object | array $parameters = [],
        int            $status = Response::HTTP_OK,
    ) : Response {

        $content = $this->render( $template, $parameters );

        return new Response(
            content : $content,
            status  : $status,
            headers : [ 'Meta-Storage' => 'local' ],
        );
    }

    /**
     * Returns a JsonResponse that uses the serializer component if enabled, or json_encode.
     *
     * - Will use any {@see SerializerInterface} assigned to {@see $this->serializer}.
     * - Pass a {@see SerializerInterface} as the last argument to override the default.
     * - If no serializer is available, json_encode will be used.
     *
     * @param mixed                     $data
     * @param int                       $status
     * @param array                     $headers
     * @param array                     $context
     * @param null|SerializerInterface  $serializer
     *
     * @return JsonResponse
     */
    final protected function json(
        mixed                $data,
        int                  $status = Response::HTTP_OK,
        array                $headers = [],
        array                $context = [],
        ?SerializerInterface $serializer = null,
    ) : JsonResponse {

        if ( null === $serializer &&
             property_exists( $this, 'serializer' ) &&
             $this->serializer instanceof SerializerInterface
        ) {
            $serializer = $this->serializer;
        }

        if ( $serializer ) {
            $context = array_merge( [ 'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS ], $context );
            $json    = $serializer->serialize( $data, 'json', $context );

            return new JsonResponse( $json, $status, $headers, true );
        }

        return new JsonResponse( $data, $status, $headers );
    }

    /**
     * Returns a {@see BinaryFileResponse} object with original or customized file name and disposition header.
     */
    protected function file(
        SplFileInfo | string $file,
        ?string              $fileName = null,
        string               $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT,
    ) : BinaryFileResponse {
        $response = new BinaryFileResponse( $file );
        $filename ??= $response->getFile()->getFilename();

        return $response->setContentDisposition( $disposition, $filename );
    }

    /**
     * Forwards the request to another controller.
     *
     * @param string  $controller  The controller name (a string like "App\Controller\PostController::index" or "App\Controller\PostController" if it is invokable)
     */
    protected function forward(
        string $controller,
        array  $path = [],
        array  $query = [],
    ) : Response {
        $request               = $this->request->current;
        $path[ '_controller' ] = $controller;
        $subRequest            = $request->duplicate( $query, null, $path );

        try {
            return static::getService( HttpKernelInterface::class )
                         ->handle( $subRequest, HttpKernelInterface::SUB_REQUEST );
        }
        catch ( Exception $e ) {
            Logger::error( $e->getMessage() );
            return new Response(
                status : Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param int  $status  The HTTP status code (302 "Found" by default)
     */
    final protected function redirect(
        string $url,
        int    $status = Response::HTTP_FOUND,
    ) : RedirectResponse {
        return new RedirectResponse( $url, $status );
    }

    /**
     * Returns a RedirectResponse to the given route with the given parameters.
     *
     * @param int  $status  The HTTP status code (302 "Found" by default)
     */
    final protected function redirectToRoute( string $route, array $parameters = [], int $status = 302,
    ) : RedirectResponse {
        $url = static::getService( RouterInterface::class )
                     ->generate( $route, $parameters );

        Logger::info(
            '{controller} is redirecting to {url}',
            [
                'controller' => $this::class,
                'url'        => $url,
            ],
        );

        return $this->redirect( $url, $status );
    }

    /**
     * @param string       $type  = ['error', 'warning', 'info', 'success'][$any]
     * @param string       $message
     * @param null|string  $description
     * @param null|int     $timeoutMs
     * @param bool         $log
     *
     * @return void
     */
    public function addFlash(
        string  $type,
        string  $message,
        ?string $description = null,
        ?int    $timeoutMs = 4500,
        bool    $log = false,
    ) : void {
        $this->request->addFlash( $type, $message, $description, $timeoutMs, $log );
    }


    /**
     * Returns a NotFoundHttpException.
     *
     * This will result in a 404 response code. Usage example:
     *
     * throw $this->createNotFoundException( `Page not found!` );
     *
     *
     * @param string      $message
     * @param ?Throwable  $previous
     *
     * @return NotFoundHttpException
     */
    final protected function createNotFoundException(
        string     $message = 'Not Found',
        ?Throwable $previous = null,
    ) : NotFoundHttpException {
        return new NotFoundHttpException( $message, $previous );
    }

    /**
     * Generate a {@see CsrfToken} for the given tokenId.
     *
     * @param string  $tokenId
     *
     * @return CsrfToken
     */
    final protected function getToken( string $tokenId ) : CsrfToken {
        return static::getService( CsrfTokenManagerInterface::class )->getToken( $tokenId );
    }

    final protected function getUser() : ?UserInterface {
        return static::getService( TokenStorageInterface::class )->getToken()?->getUser();
    }

    final protected function dynamicTemplatePath( ?string $dir = null ) : string {

        $dir  ??= defined( static::class . '::DYNAMIC_TEMPLATE_DIR' ) ? static::DYNAMIC_TEMPLATE_DIR : '';
        $file = str_replace( '/', '.', $this->request->route ) . '.latte';

        return Path::normalize( $dir . DIRECTORY_SEPARATOR . $file );
    }
}