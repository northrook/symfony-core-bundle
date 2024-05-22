<?php

namespace Northrook\Symfony\Core\Services;

use Northrook\Logger\Log\Timestamp;
use Northrook\Symfony\Components\Component\Notification;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

final readonly class NotificationService
{
    public function __construct(
        private CurrentRequestService $request,
        private ParameterBagInterface $parameterBag,
    ) {}


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
     * Add notification element to a string, usually the content passed to a {@see Response}.
     *
     * - Will be appended by default, unless `$prepend` is set to `true`.
     * - Notifications will be added as raw HTML, you can handle the front-end as you see fit.
     *
     * @param string  $content
     * @param bool    $prepend
     *
     * @return string
     */
    public function injectFlashBagNotifications( string $content = '', bool $prepend = false ) : string {


        $flashBag = $this->request->flashBag();

        if ( $flashBag->peekAll() ) {
            $notifications = [];

            foreach ( $this->parseFlashBag( $flashBag ) as $flash ) {

                $notification = new Notification(
                    $flash[ 'level' ],
                    $flash[ 'message' ],
                    $flash[ 'description' ],
                    $flash[ 'timeout' ],
                    $flash[ 'timestamp' ],
                );

                $notifications[] = PHP_EOL . $notification->print( true );
            }

            if ( !empty( $notifications ) ) {
                $content = $prepend
                    ? implode( PHP_EOL, $notifications ) . PHP_EOL . $content
                    : $content . PHP_EOL . implode( PHP_EOL, $notifications );
            }
        }
        return $content;
    }

    private function parseFlashBag( FlashBagInterface $flashBag ) : array {

        $flashes       = array_merge( ... array_values( $flashBag->all() ) );
        $notifications = [];

        foreach ( $flashes as $value ) {
            $level       = $value[ 'level' ];
            $message     = $value[ 'message' ];
            $description = $value[ 'description' ];
            $timeout     = $value[ 'timeout' ];

            /** @var   Timestamp $timestamp */
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

}