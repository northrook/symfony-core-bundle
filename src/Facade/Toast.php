<?php

namespace Northrook\Symfony\Core\Facade;

use Northrook\Symfony\Core\DependencyInjection\Facade;
use Northrook\Symfony\Toast as Notification;
use Northrook\Symfony\ToastManager;

final class Toast extends Facade
{
    private static function manager() : ToastManager {
        return Toast::getService( ToastManager::class );
    }

    /**
     * @param string       $type  = ['info', 'success', 'warning', 'error', 'notice'][$any]
     * @param string       $message
     * @param null|string  $description
     * @param null|int     $timeoutMs
     *
     * @return Notification
     */
    public static function message( string $type, string $message, ?string $description = null, ?int $timeoutMs = null,
    ) : Notification {
        return Toast::manager()->addToast( $type, $message, $description, $timeoutMs );
    }

    /**
     * @param string       $message
     * @param null|string  $description
     * @param null|int     $timeoutMs
     *
     * @return Notification
     */
    public static function info( string $message, ?string $description = null, ?int $timeoutMs = null,
    ) : Notification {
        return Toast::manager()->addToast( 'info', $message, $description, $timeoutMs );
    }

    /**
     * @param string       $message
     * @param null|string  $description
     * @param null|int     $timeoutMs
     *
     * @return Notification
     */
    public static function success( string $message, ?string $description = null, ?int $timeoutMs = null,
    ) : Notification {
        return Toast::manager()->addToast( 'success', $message, $description, $timeoutMs );
    }

    /**
     * @param string       $message
     * @param null|string  $description
     * @param null|int     $timeoutMs
     *
     * @return Notification
     */
    public static function warning( string $message, ?string $description = null, ?int $timeoutMs = null,
    ) : Notification {
        return Toast::manager()->addToast( 'warning', $message, $description, $timeoutMs );
    }

    /**
     * @param string       $message
     * @param null|string  $description
     * @param null|int     $timeoutMs
     *
     * @return Notification
     */
    public static function error( string $message, ?string $description = null, ?int $timeoutMs = null,
    ) : Notification {
        return Toast::manager()->addToast( 'error', $message, $description, $timeoutMs );
    }

    /**
     * @param string       $message
     * @param null|string  $description
     * @param null|int     $timeoutMs
     *
     * @return Notification
     */
    public static function notice( string $message, ?string $description = null, ?int $timeoutMs = null,
    ) : Notification {
        return Toast::manager()->addToast( 'notice', $message, $description, $timeoutMs );
    }
}