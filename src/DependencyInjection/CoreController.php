<?php

namespace Northrook\Symfony\Core\DependencyInjection;

use Exception;
use Northrook\Core\Trait\PropertyAccessor;
use Northrook\Latte;
use Northrook\Logger\Log;
use Northrook\Symfony\Core\Autowire\CurrentRequest;
use Northrook\Symfony\Core\ErrorHandler\ErrorEventException;
use Northrook\Symfony\Core\Facade\Request;
use Northrook\Symfony\Core\Facade\URL;
use Stringable;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use function Northrook\normalizePath;

/**
 * @property-read HttpKernelInterface $httpKernel
 * @property-read LatteBundle         $latte
 *
 * @author  Martin Nielsen <mn@northrook.com>
 *
 * @link    https://github.com/northrook Documentation
 *
 * @internal
 */
abstract class CoreController
{
    use PropertyAccessor;


    protected readonly CurrentRequest $request;

    final public function setCurrentRequest( CurrentRequest $currentRequest ) : void {
        $this->request ??= $currentRequest;
    }

    private function getHttpKernel() : HttpKernelInterface {
        return ServiceContainer::get( HttpKernelInterface::class );
    }

    private function getLatteBundle() : Latte {
        return ServiceContainer::get( Latte::class );
    }

    public function __get( string $property ) {
        return match ( $property ) {
            'latte'      => $this->getLatteBundle(),
            'httpKernel' => $this->getHttpKernel(),
        };
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

        dump( $this->request->flashBag() );
        
        return new Response(
            content : $this->getLatteBundle()->render( $template, $parameters ),
            status  : $status,
            headers : [ 'Meta-Storage' => 'test' ],
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
             \property_exists( $this, 'serializer' ) &&
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
    final protected function file(
        SplFileInfo | string $file,
        ?string              $fileName = null,
        string               $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT,
    ) : BinaryFileResponse {
        $response = new BinaryFileResponse( $file );
        $fileName ??= $response->getFile()->getFilename();

        return $response->setContentDisposition( $disposition, $fileName );
    }

    /**
     * Forwards the request to another controller.
     *
     * @param string|class-string  $controller  The controller name (a string like "App\Controller\PostController::index" or "App\Controller\PostController" if it is invokable)
     */
    final protected function forward(
        string $controller,
        array  $path = [],
        array  $query = [],
    ) : Response {
        $request               = Request::current();
        $path[ '_controller' ] = $controller;
        $subRequest            = $request->duplicate( $query, null, $path );

        try {
            return $this->getHttpKernel()->handle( $subRequest, HttpKernelInterface::SUB_REQUEST );
        }
        catch ( Exception $e ) {
            Log::error( $e->getMessage() );
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
    final protected function redirectToRoute(
        string $route,
        array  $parameters = [],
        int    $status = 302,
    ) : RedirectResponse {

        try {
            $url = URL::get( $route, $parameters );
            Log::info( '{controller} is redirecting to {url}', [ 'controller' => $this::class, 'url' => $url ] );
            return $this->redirect( $url, $status );
        }
        catch ( Exception $exception ) {
            throw new ErrorEventException( previous : $exception );
        }
    }

    /**
     * Adds a simple flash message to the current session.
     *
     * @param string                   $type  = ['info', 'success', 'warning', 'error', 'notice'][$any]
     * @param string|Stringable|array  $message
     *
     * @return void
     */
    public function addFlash(
        string $type, string | Stringable | array $message,
    ) : void {
        Request::addFlash( $type, $message );
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
        return ServiceContainer::get( TokenStorageInterface::class )->getToken()?->getUser();
    }

    final protected function dynamicTemplatePath( ?string $dir = null ) : string {

        $dir  ??= defined( static::class . '::DYNAMIC_TEMPLATE_DIR' ) ? static::DYNAMIC_TEMPLATE_DIR : '';
        $file = str_replace( '/', '.', $this->request->route ) . '.latte';

        return normalizePath( $dir . DIRECTORY_SEPARATOR . $file );
    }
}