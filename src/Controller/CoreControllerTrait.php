<?php

namespace Northrook\Symfony\Core\Controller;

use LogicException;
use Northrook\Elements\Render\Template;
use Northrook\Logger\Log;
use Northrook\Symfony\Core\App;
use Northrook\Symfony\Core\Security\ErrorEventException;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Throwable;

trait CoreControllerTrait
{

    private readonly ?string $currentPath;
    private readonly ?string $currentRoute;


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


    final protected function route( ?string $is = null ) : string | bool {

        $this->currentPath  ??= $this->request->pathInfo;
        $this->currentRoute ??= trim( $this->currentPath, '/' );

        if ( $is ) {
            return $this->currentRoute === $is;
        }

        return $this->currentRoute;
    }

    /**
     * Adds a flash message to the current session for type.
     *
     * @throws LogicException
     */
    final protected function addFlash( string $type, mixed $message ) : void {
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