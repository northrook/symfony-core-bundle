<?php

namespace Northrook\Symfony\Core\DependencyInjection\Trait;


use JetBrains\PhpStorm\Deprecated;
use Latte;
use Northrook\Symfony\Core\DependencyInjection\CoreDependencies;
use Northrook\Symfony\Core\Security\ErrorEventException;
use Northrook\Types\Path;

/**
 * @property CoreDependencies $get
 */
#[Deprecated]
trait LatteRenderer
{
    private readonly string $dynamicTemplatePath;

    /**
     * Render a `.latte` template to string.
     *
     * @param string        $template
     * @param object|array  $parameters
     *
     * @return string
     */
    public function render(
        string         $template,
        object | array $parameters = [],
    ) : string {

        try {
            $content = $this->latte->render(
                template   : $template,
                parameters : $parameters,
            );
        }
        catch ( Latte\RuntimeException $e ) {
            throw new ErrorEventException(
                message  : $e->getMessage(),
                previous : $e,
            );
        }

        return $this->injectFlashBagNotifications( $content );
    }

    // public function injectFlashBagNotifications( string $string = '' ) : string {
    //
    //     $flashBag = $this->request->flashBag();
    //
    //     if ( $flashBag->peekAll() ) {
    //         $notifications = [];
    //
    //         foreach ( $this->parseFlashBag( $flashBag ) as $notification ) {
    //             $notifications[] = PHP_EOL . ( new Notification(
    //                     $notification[ 'level' ],
    //                     $notification[ 'message' ],
    //                     $notification[ 'description' ],
    //                     $notification[ 'timeout' ],
    //                     $notification[ 'timestamp' ],
    //                 ) )->print( true );
    //         }
    //
    //         $string .= PHP_EOL . implode( PHP_EOL, $notifications );
    //
    //
    //     }
    //     return $string;
    // }

    // private function parseFlashBag( FlashBagInterface $flashBag ) : array {
    //
    //     $flashes       = array_merge( ... array_values( $flashBag->all() ) );
    //     $notifications = [];
    //     foreach ( $flashes as $value ) {
    //         $level       = $value[ 'level' ];
    //         $message     = $value[ 'message' ];
    //         $description = $value[ 'description' ];
    //         $timeout     = $value[ 'timeout' ];
    //
    //         /** @var   Timestamp $timestamp */
    //         $timestamp = $value[ 'timestamp' ];
    //
    //         if ( isset( $notifications[ $message ] ) ) {
    //             $notifications[ $message ][ 'timestamp' ][ $timestamp->timestamp ] = $timestamp;
    //         }
    //
    //         else {
    //             $notifications[ $message ] = [
    //                 'level'       => $level,
    //                 'message'     => $message,
    //                 'description' => $description,
    //                 'timeout'     => $timeout,
    //                 'timestamp'   => [ $timestamp->timestamp => $timestamp, ],
    //             ];
    //         }
    //     }
    //
    //     usort(
    //         $notifications, static fn ( $a, $b ) => ( end( $a[ 'timestamp' ] ) ) <=> ( end( $b[ 'timestamp' ] ) ),
    //     );
    //
    //     return $notifications;
    // }

    final protected function dynamicTemplatePath() : string {
        if ( !isset( $this->dynamicTemplatePath ) ) {
            $dir  = defined( static::class . '::DYNAMIC_TEMPLATE_DIR' ) ? static::DYNAMIC_TEMPLATE_DIR : '';
            $file = str_replace( '/', '.', $this->get->request->route ) . '.latte';

            $this->dynamicTemplatePath = Path::normalize( $dir . DIRECTORY_SEPARATOR . $file );
        }

        return $this->dynamicTemplatePath;


    }
}