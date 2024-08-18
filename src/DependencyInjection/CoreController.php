<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\DependencyInjection;

use Exception;
use JetBrains\PhpStorm\Deprecated;
use Northrook\Latte;
use Northrook\Latte\Runtime\ComponentAssetHandler;
use Northrook\Logger\Log;
use Northrook\Symfony\Core\Autowire\CurrentRequest;
use Northrook\Symfony\Core\ErrorHandler\ErrorEventException;
use Northrook\Symfony\Core\Facade\Request;
use Northrook\Symfony\Core\Facade\URL;
use Northrook\Symfony\Service\Document\DocumentService;
use Northrook\Symfony\Service\Toasts\Message;
use Stringable;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use function Northrook\normalizePath;
use function Northrook\toString;

/**
 * @property-read HttpKernelInterface $httpKernel
 *
 * @author  Martin Nielsen <mn@northrook.com>
 *
 * @link    https://github.com/northrook Documentation
 *
 * @internal
 */
abstract class CoreController
{
    protected readonly CurrentRequest $request;

    public bool $isPublic = false;

    /**
     * Return a {@see Response}`view` from a `.latte` template.
     *
     * @param string        $content
     * @param object|array  $parameters
     * @param int           $status
     *
     * @return Response
     */
    final protected function response(
        string         $content,
        object | array $parameters = [],
        int            $status = Response::HTTP_OK,
        ?Latte         $engine = null,
    ) : Response {

        if ( \str_ends_with( $content, '.latte' ) ) {
            if ( !$engine ??= ServiceContainer::get( Latte::class ) ?? null ) {
                throw new \LogicException(
                    "A templating engine is required to use the Response method. 
                Please inject '" . Latte::class . "' into to the '__construct' method.
                Alternatively, you can inject it directly into the controller method, 
                and pass it as the fourth argument to this Response method.",
                );
            }

            $content = $engine->render( $content, $parameters );
        }

        return new Response(
            content : $this->responseContent( $content ),
            status  : $this->responseStatus( $status ),
            headers : $this->responseHeaders(),
        );
    }

    private function responseContent( ?string $content ) : string {
        $notifications = $this->handleFlashBag();
        if ( \property_exists( $this, 'document' )
             &&
             $this->document instanceof DocumentService ) {
            dump( ComponentAssetHandler::getAssets() );
            return $this->document->renderDocumentHtml( $content, $notifications );
        }

        return $notifications . $content;
    }


    // protected function renderView( ?string $content ) : string {
    //
    // }

    private function responseStatus( int $assume ) : int {
        return Response::HTTP_OK;
    }

    private function responseHeaders() : array {
        $headers = [];
        if ( !$this->isPublic ) {
            $headers[ 'X-Robots-Tag' ] = 'noindex, nofollow';
        }
        return $headers;
    }

    private function handleFlashBag() : string {
        $notifications = '';

        foreach ( $this->request->flashBag()->all() as $type => $flash ) {
            foreach ( $flash as $toast ) {
                if ( $toast instanceof Message ) {
                    $notifications .= new Latte\Component\Notification(
                        $toast->type,
                        $toast->message,
                        $toast->description,
                        $toast->timeout,
                    );
                }
                else {
                    $notifications .= new Latte\Component\Notification(
                                  $type,
                                  toString( $toast ),
                        timeout : $type !== 'danger' ? 15 : null,
                    );
                }
            }
        }

        return $notifications;
    }

    #[Deprecated]
    private function templateParameters( object | array $parameters ) : object | array {

        if ( \is_object( $parameters ) ) {
            return $parameters;
        }

        if ( isset( $parameters[ 'document' ] ) ) {
            if ( $parameters[ 'document' ] instanceof DocumentService ) {
                return $parameters;
            }
            else {
                throw new \InvalidArgumentException(
                    "The 'document' parameter is reserved for this controller.",
                );
            }
        }

        if ( \property_exists( $this, 'document' )
             && $this->document instanceof DocumentService ) {
            $parameters = [ 'document' => $this->document->getDocumentParameters(), ... $parameters ];
        }

        return $parameters;
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
        $request               = $this->request->current;
        $path[ '_controller' ] = $controller;
        $subRequest            = $request->duplicate( $query, null, $path );

        try {
            return $this->request->httpKernel()
                                 ->handle( $subRequest, HttpKernelInterface::SUB_REQUEST );
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
     * ```
     * throw $this->createNotFoundException( `Page not found!` );
     *  ```
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
     * Returns an AccessDeniedException.
     *
     * This will result in a 403 response code. Usage example:
     *
     *  ```
     * throw $this->createAccessDeniedException('Access Denied!');
     *  ```
     */
    final protected function createAccessDeniedException(
        string      $message = 'Access Denied',
        ?\Throwable $previous = null,
    ) : AccessDeniedException {
        return new AccessDeniedException( $message, $previous );
    }


    final protected function dynamicTemplatePath( ?string $dir = null ) : string {

        $dir  ??= defined( static::class . '::DYNAMIC_TEMPLATE_DIR' ) ? static::DYNAMIC_TEMPLATE_DIR : '';
        $file = str_replace( '/', '.', $this->request->route ) . '.latte';

        return normalizePath( $dir . DIRECTORY_SEPARATOR . $file );
    }
}