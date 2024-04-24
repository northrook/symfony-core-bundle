<?php

namespace Northrook\Symfony\Core\Controller;

use Exception;
use LogicException;
use Northrook\Elements\Render\Template;
use Northrook\Logger\Log;
use Northrook\Symfony\Core\App;
use Northrook\Symfony\Core\Components\Notification;
use Northrook\Symfony\Core\Security\ErrorEventException;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\SecurityService;
use SensitiveParameter;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Throwable;

/**
 * @property RouterInterface       $router    ;
 * @property HttpKernelInterface   $httpKernel;
 * @property CurrentRequestService $request   ;
 * @property SecurityService       $security  ;
 */
trait CoreControllerTrait
{

    private readonly ?string $currentPath;
    private readonly ?string $currentRoute;

    protected readonly array $properties;


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
            headers : [ 'Meta-Storage' => 'local' ],
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

        $parameters = array_merge( $this->properties ?? [], $parameters );


        // dd( $parameters);
        $content = $this->latte->render(
            template   : $template,
            parameters : $parameters,
        );

        $content = $this->injectFlashBagNotifications( $content );


        return $content;
    }

    /**
     * Forwards the request to another controller.
     *
     * @param string  $controller  The controller name (a string like "App\Controller\PostController::index" or "App\Controller\PostController" if it is invokable)
     */
    protected function forward( string $controller, array $path = [], array $query = [] ) : Response {
        $request               = $this->request->current;
        $path[ '_controller' ] = $controller;
        $subRequest            = $request->duplicate( $query, null, $path );

        try {
            return $this->httpKernel->handle( $subRequest, HttpKernelInterface::SUB_REQUEST );
        }
        catch ( Exception $e ) {
            $this->logger?->error( $e->getMessage() );
            return new Response(
                status : Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * Returns a JsonResponse that uses the serializer component if enabled, or json_encode.
     *
     * @param int  $status  The HTTP status code (200 "OK" by default)
     */
    protected function json( mixed $data, int $status = 200, array $headers = [], array $context = [] ) : JsonResponse {
        if ( $this->serializer ) {
            $context = array_merge( [ 'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS ], $context );
            $json    = $this->serializer->serialize(
                $data,
                'json',
                $context,
            );

            return new JsonResponse( $json, $status, $headers, true );
        }

        return new JsonResponse( $data, $status, $headers );
    }

    /**
     * Returns a BinaryFileResponse object with original or customized file name and disposition header.
     */
    protected function file(
        SplFileInfo | string $file,
        ?string              $fileName = null,
        string               $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT,
    ) : BinaryFileResponse {
        $response = new BinaryFileResponse( $file );
        $response->setContentDisposition(
            $disposition,
            $fileName ?? $response->getFile()->getFilename(),
        );

        return $response;
    }


    private function parseFlashBag( FlashBagInterface $flashBag ) : array {

        $flashes       = array_merge( ... array_values( $flashBag->all() ) );
        $notifications = [];
        foreach ( $flashes as $value ) {
            $level       = $value[ 'level' ];
            $message     = $value[ 'message' ];
            $description = $value[ 'description' ];
            $timeout     = $value[ 'timeout' ];

            /** @var   Log\Timestamp $timestamp */
            $timestamp = $value[ 'timestamp' ];

            if ( isset( $notifications[ $message ] ) ) {
                $notifications[ $message ][ 'timestamp' ][ $timestamp->timestamp ] = $timestamp;
            }
            else {
                $notifications[ $message ] = [
                    'level'       => $level,
                    'message'     => $message,
                    'description' => $description,
                    'timeout'     => $timeout,
                    'timestamp'   => [ $timestamp->timestamp => $timestamp, ],
                ];
            }
        }

        usort(
            $notifications, static fn ( $a, $b ) => ( end( $a[ 'timestamp' ] ) ) <=> ( end( $b[ 'timestamp' ] ) ),
        );

        return $notifications;
    }

    private function injectFlashBagNotifications( string $string ) : string {

        $flashBag = $this->request->flashBag();
        if ( $flashBag->peekAll() ) {
            $notifications = [];

            foreach ( $this->parseFlashBag( $flashBag ) as $notification ) {
                $notifications[] = (string) new Notification(
                    $notification[ 'level' ],
                    $notification[ 'message' ],
                    $notification[ 'description' ],
                    $notification[ 'timeout' ],
                    $notification[ 'timestamp' ],
                );
            }

            // if ( Str::contains( $string, ['<body', '</body>'])) {
            //
            // }

            $string .= implode( '', $notifications );


        }
        return $string;
    }


    final protected function route( ?string $is = null ) : string | bool {

        $this->currentPath  ??= $this->request->pathInfo;
        $this->currentRoute ??= trim( $this->currentPath, '/' );

        if ( $is ) {
            return $this->currentRoute === $is;
        }

        return $this->currentRoute;
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
     * Returns a RedirectResponse to the given URL.
     *
     * @param int  $status  The HTTP status code (302 "Found" by default)
     */
    final protected function redirect( string $url, int $status = 302 ) : RedirectResponse {
        return new RedirectResponse( $url, $status );
    }

    /**
     * Returns a RedirectResponse to the given route with the given parameters.
     *
     * @param int  $status  The HTTP status code (302 "Found" by default)
     */
    final protected function redirectToRoute( string $route, array $parameters = [], int $status = 302,
    ) : RedirectResponse {
        return $this->redirect( $this->generateUrl( $route, $parameters ), $status );
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @see UrlGeneratorInterface
     */
    final protected function generateUrl(
        string $route,
        array  $parameters = [],
        int    $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
    ) : string {

        return $this->router->generate( $route, $parameters, $referenceType );
    }

    /**
     * Returns a NotFoundHttpException.
     *
     * This will result in a 404 response code. Usage example:
     *
     *     throw $this->createNotFoundException('Page not found!');
     */
    protected function createNotFoundException(
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
     *     throw $this->createAccessDeniedException('Unable to access this page!');
     *
     * @throws LogicException If the Security component is not available
     */
    protected function createAccessDeniedException(
        string     $message = 'Access Denied.',
        ?Throwable $previous = null,
    ) : AccessDeniedException {
        return new AccessDeniedException( $message, $previous );
    }

    /**
     * Get a user from the Security Token Storage.
     *
     * @throws LogicException If SecurityBundle is not available
     *
     * @see TokenInterface::getUser()
     */
    protected function getUser() : ?UserInterface {

        if ( null === $token = $this->security->tokenStorage->getToken() ) {
            return null;
        }

        return $token->getUser();
    }

    /**
     * Checks if the attribute is granted against the current authentication token and optionally supplied subject.
     *
     */
    protected function isGranted( mixed $attribute, mixed $subject = null ) : bool {
        return $this->security->authorization->isGranted( $attribute, $subject );
    }

    /**
     * Checks the validity of a CSRF token.
     *
     * @param string       $id     The id used when generating the token
     * @param string|null  $token  The actual token sent with the request that should be validated
     */
    protected function isCsrfTokenValid(
        string $id, #[SensitiveParameter]
    ?string    $token,
    ) : bool {

        return $this->security->csrf->isTokenValid( new CsrfToken( $id, $token ) );
    }


    /**
     * Throws an exception unless the attribute is granted against the current authentication token and optionally
     * supplied subject.
     *
     * @throws AccessDeniedException
     */
    protected function denyAccessUnlessGranted(
        mixed $attribute, mixed $subject = null, string $message = 'Access Denied.',
    ) : void {
        if ( !$this->isGranted( $attribute, $subject ) ) {
            $exception = $this->createAccessDeniedException( $message );
            $exception->setAttributes( [ $attribute ] );
            $exception->setSubject( $subject );

            throw $exception;
        }
    }

    private function dependencyValidation( string $property, ?string $className ) : void {

        if ( isset( $this->$property ) ) {

            Log::Alert(
                'Dependency Injection Error - {property} not injected in {class}.',
                [
                    'property'  => $property,
                    'className' => $className,
                    'class'     => $this::class,
                ],
            );

            $message = App::env( 'public' ) ? "Not found" : 'Dependency Injection Error';
            $blurb   = 'The "' . $className . '" property has not been injected into "' . $this::class . '"';

            throw new ErrorEventException(
                $message,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                new Template(
                    '<span>{status}</span><h1>{message}</h1>{blurb|nl2auto}',
                    [
                        'status'  => $status = Response::HTTP_INTERNAL_SERVER_ERROR,
                        'message' => $message,
                        'blurb'   => $blurb,
                    ],
                ),
            );
        }

    }
}