<?php

namespace Northrook\Symfony\Core\Controller;

use Exception;
use LogicException;
use Northrook\Logger\Status\HTTP;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\SecurityService;
use Psr\Log\LoggerInterface;
use SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Throwable;

readonly abstract class AbstractCoreControllerMethods
{
    private readonly ?string $currentPath;
    private readonly ?string $currentRoute;

    protected RouterInterface       $router;
    protected SecurityService       $security;
    protected CurrentRequestService $request;
    protected ?LoggerInterface      $logger;


    final protected function route( ?string $is = null ) : string | bool {

        $this->currentPath  ??= $this->request->pathInfo;
        $this->currentRoute ??= trim( $this->currentPath, '/' );

        if ( $is ) {
            return $this->currentRoute === $is;
        }

        return $this->currentRoute;
    }

    protected function response(
        string         $template,
        object | array $parameters = [],
        int | HTTP     $status = HTTP::OK,
    ) : Response {

        if ( is_array( $parameters ) && isset( $this->document ) ) {
            $parameters[ 'document' ] = $this->document;
        }

        return new Response(
            content : $this->render( $template, $parameters ),
            status  : $status,
        );
    }

    protected function render(
        string         $template,
        object | array $parameters = [],
    ) : string {

        return $this->latte->render(
            template   : $template,
            parameters : $parameters,
        );
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @see UrlGeneratorInterface
     */
    protected function generateUrl(
        string $route,
        array  $parameters = [],
        int    $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
    ) : string {
        return $this->router->generate( $route, $parameters, $referenceType );
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
                status : HTTP::INTERNAL_SERVER_ERROR,
            );
        }
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

    /**
     * Adds a flash message to the current session for type.
     *
     * @throws LogicException
     */
    protected function addFlash( string $type, mixed $message ) : void {
        try {
            $session = $this->request->session();
        }
        catch ( SessionNotFoundException $e ) {
            throw new LogicException(
                'You cannot use the addFlash method if sessions are disabled. Enable them in "config/packages/framework.yaml".',
                0,
                $e,
            );
        }

        if ( !$session instanceof FlashBagAwareSessionInterface ) {
            throw new LogicException(
                sprintf(
                    'You cannot use the addFlash method because class "%s" doesn\'t implement "%s".',
                    get_debug_type( $session ), FlashBagAwareSessionInterface::class,
                ),
            );
        }

        $session->getFlashBag()->add( $type, $message );
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
        string $id, #[\SensitiveParameter]
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

}