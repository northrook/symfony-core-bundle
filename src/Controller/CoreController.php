<?php

namespace Northrook\Symfony\Core\Controller;

use Exception;
use JetBrains\PhpStorm\Deprecated;
use LogicException;
use Northrook\Support\Str;
use Northrook\Symfony\Core\Latte\LatteRenderTrait;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\DocumentService;
use Northrook\Symfony\Core\Services\PathfinderService;
use Northrook\Symfony\Core\Services\SecurityService;
use Northrook\Symfony\Core\Services\SettingsManagementService;
use Northrook\Symfony\Latte\Core\Environment;
use Northrook\Types\Path;
use Psr\Log\LoggerInterface;
use SensitiveParameter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

#[Deprecated]
abstract class CoreController
{
    use LatteRenderTrait;

    /** @var ?string in `~/templates/{dir}` */
    public const DYNAMIC_TEMPLATE_DIR = null;
    private readonly RouterInterface      $router;
    private readonly HttpKernelInterface  $httpKernel;
    private readonly ?Environment         $latte;
    private readonly ?SerializerInterface $serializer;
    private ?string                       $dynamicTemplatePath = null;
    /** @var array{nane: string, value: string} */
    private array                                $headers = [];
    protected readonly CurrentRequestService     $request;
    protected readonly DocumentService           $document;
    protected readonly SettingsManagementService $settings;
    protected readonly SecurityService           $security;
    protected readonly PathfinderService         $pathfinder;
    protected readonly ?LoggerInterface          $logger;
    public readonly string                       $route;
    public readonly string                       $prefix;
    public readonly ?string                      $currentPath;
    public readonly ?string                      $currentRoute;

    public function __construct() {
        $this->route        = $this->request->parameter( 'route' ) ?? '/';
        $this->currentPath  ??= $this->request->pathInfo;
        $this->currentRoute ??= trim( $this->currentPath, '/' );
        $this->prefix       = strstr( $this->currentRoute, $this->route, true );
    }

    public function setControllerDependencies(
        RouterInterface      $router,
        HttpKernelInterface  $kernel,
        ?Environment         $latte = null,
        ?SerializerInterface $serializer = null,
    ) : void {
        $this->router     = $router;
        $this->httpKernel = $kernel;
        $this->latte      = $latte;
        $this->serializer = $serializer;
    }

    protected function dynamicTemplatePath() : string {
        if ( null === $this->dynamicTemplatePath ) {
            $file = str_replace( '/', '.', $this->route ) . '.latte';
            $path = $this::DYNAMIC_TEMPLATE_DIR . '/' . $file;

            $this->dynamicTemplatePath = Path::normalize( $path );
        }

        return $this->dynamicTemplatePath;


    }

    /**
     * Get the {@see CoreController::$headers}.
     *
     * Prepended to the {@see ResponseHeaderBag} of {@see static::response} and {@see static::json}.
     *
     * @param array{nane:string,value:string}  $add
     *
     * @return array
     */
    final public function getHeaders( array $add = [] ) : array {
        return array_merge( $this->headers, $add );
    }

    /**
     * @param string  $name
     * @param string  $value
     * @param bool    $replace
     *
     * @return $this
     */
    final public function header( string $name, string $value, bool $replace = true ) : self {

        $key = Str::key( $name );

        if ( !$key ) {
            $this->logger->error(
                'Provided {name} header key for {value} is invalid. It returned null or empty after sanitizing',
                [ 'value' => $value, 'name' => $name ],
            );

            return $this;
        }

        if ( false === $replace && array_key_exists( $name, $this->headers ) ) {
            return $this;
        }

        $this->headers[ $name ] = $value;

        return $this;
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
     * Return a {@see Response}`view` from a `.latte` template.
     *
     * @param string                           $template
     * @param object|array                     $parameters
     * @param int                              $status
     * @param array{nane:string,value:string}  $headers
     *
     * @return Response
     */
    final protected function response(
        string         $template,
        object | array $parameters = [],
        int            $status = Response::HTTP_OK,
        array          $headers = [],
    ) : Response {


        // dd( $parameters, $this );
        if ( !array_key_exists( 'template', $parameters ) ) {
            $parameters[ 'template' ] = str_replace( '/', '.', trim( $this->currentRoute, '/' ) ) . '.latte';;
        }

        return new Response(
            content : $this->render( $template, $parameters ),
            status  : $status,
            headers : $this->getHeaders( $headers ),
        );
    }


    /**
     * Returns a JsonResponse that uses the serializer component if enabled, or json_encode.
     *
     * @param int  $status  The HTTP status code (200 "OK" by default)
     */
    final protected function json( mixed $data, int $status = 200, array $headers = [], array $context = [],
    ) : JsonResponse {


        if ( $this->serializer ) {
            $context = array_merge( [ 'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS ], $context );
            $json    = $this->serializer->serialize( $data, 'json', $context, );

            return new JsonResponse( $json, $status, $this->getHeaders( $headers ), true );
        }

        return new JsonResponse( $data, $status, $this->getHeaders( $headers ) );
    }

    /**
     * Forwards the request to another controller.
     *
     * @param string  $controller  The controller name (a string like "App\Controller\PostController::index" or "App\Controller\PostController" if it is invokable)
     */
    final protected function forward( string $controller, array $path = [], array $query = [] ) : Response {
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
     * Checks if the attribute is granted against the current authentication token and optionally supplied subject.
     *
     */
    protected function isGranted( mixed $attribute, mixed $subject = null ) : bool {
        return $this->security->authorization->isGranted( $attribute, $subject );
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