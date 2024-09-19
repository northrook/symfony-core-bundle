<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\ResponseHandler;

use Northrook\Get;
use Northrook\Latte;
use Northrook\Settings;
use Northrook\Symfony\Core\DependencyInjection\ServiceContainer;
use Northrook\Symfony\Core\Service\CurrentRequest;
use Northrook\Symfony\Service\Document\DocumentService;
use Northrook\Symfony\Service\Toasts\Message;
use Northrook\Trait\PropertyAccessor;
use Northrook\UI\AssetHandler;
use Northrook\UI\Component\Notification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function Northrook\toString;
use const Northrook\EMPTY_STRING;


/**
 * @property-read DocumentService $document
 *
 * @internal
 */
final class RenderPayload extends Response
{
    use PropertyAccessor;


    private ?DocumentService $documentService = null;
    private bool             $rendered        = false;

    /**
     * @param string        $content
     * @param object|array  $parameters
     * @param int           $status
     * @param array         $headers
     */
    public function __construct(
            string                 $content,
            private array | object $parameters = [],
            int                    $status = Response::HTTP_OK,
            array                  $headers = [],
    )
    {
        parent::__construct( $content, $status, $headers );
    }

    public function prepare( Request $request ) : static
    {
        $this->renderPayload();
        return parent::prepare( $request ); // TODO: Change the autogenerated stub
    }

    protected function request() : CurrentRequest
    {
        return ServiceContainer::get( CurrentRequest::class );
    }

    public function __get( string $property )
    {
        return match ( $property ) {
            'document' => $this->documentService ??= ServiceContainer::get( DocumentService::class ),
            default    => null,
        };
    }

    public function isPublic( bool $set ) : RenderPayload
    {
        $this->document->isPublic = $set;

        return $this;
    }

    public function addParameter( string $key, $value ) : RenderPayload
    {
        $this->parameters[ $key ] = $value;
        return $this;
    }

    private function renderPayload() : void
    {
        if ( $this->rendered || $this->payloadIsArbitraryString() ) {
            return;
        }

        $this->content = $this->renderContent();

        $notifications = $this->flashBagHandler();

        $this->assetHandler();

        if ( $this->documentService ) {
            $this->content = $this->documentService->renderDocumentHtml(
                    $this->content, $notifications,
            );
        }
        else {
            $this->content = $notifications . $this->content;
        }

        $this->rendered = true;
    }

    private function renderContent() : string
    {
        $latte = $this->latteEnvironment();

        if ( !$this->documentService ) {
            return $latte->render(
                    template   : $this->content,
                    parameters : $this->parameters,
            );
        }

        $layout = \strstr( $this->content, '/', true );

        $this->documentService->document->add( 'body.id', $layout );

        $this->parameters[ 'template' ] = $this->content;
        $this->parameters[ 'document' ] = $this->documentService;

        return $latte->render(
                template   : "$layout.latte",
                parameters : $this->parameters,
        );
    }

    private function latteEnvironment() : Latte
    {
        $latte = ServiceContainer::get( Latte::class );

        // if ( !Env::isProduction() ) {
        // $latte->clearTemplateCache();
        // }
        // else {
        //     Log::critical(
        //             'Do not perform {method} on every Latte render in production.',
        //             [ 'method' => '$latte->clearTemplateCache()', ],
        //     );
        // }

        return $latte;
    }

    private function payloadIsArbitraryString() : bool
    {
        return !\str_ends_with( $this->content, '.latte' );
    }

    private function flashBagHandler() : string
    {
        $flashes       = $this->request()->flashBag()->all();
        $notifications = EMPTY_STRING;

        foreach ( $flashes as $type => $flash ) {
            foreach ( $flash as $toast ) {
                $notification = $this->resolveFlash( $toast, $type );

                if ( !$notification->description ) {
                    $notification->attributes->add( 'class', 'compact' );
                }

                if ( !$notification->timeout && $notification->type !== 'danger' ) {
                    $notification->setTimeout( Settings::get( 'notification.timeout' ) ?? 5000 );
                }

                $notifications .= $notification;
            }
        }

        return $notifications;
    }

    private function resolveFlash( mixed $toast, int | string $type ) : Notification
    {
        if ( $toast instanceof Message ) {
            return new Notification(
                    $toast->type,
                    $toast->message,
                    $toast->description,
                    $toast->timeout,
            );
        }
        return new Notification( $type, toString( $toast ) );
    }

    private function assetHandler() : void
    {
        $runtimeAssets = ( new AssetHandler( Get::path( 'dir.assets' ) ) )->getComponentAssets();

        if ( $this->documentService ) {
            $this->documentService->asset( $runtimeAssets );
            return;
        }

        if ( $this->request()->isHtmx ) {
            $assets = EMPTY_STRING;

            foreach ( $runtimeAssets as $asset ) {
                $assets .= $asset->getInlineHtml( true );
            }

            $this->content = $assets . $this->content;
        }
    }

}