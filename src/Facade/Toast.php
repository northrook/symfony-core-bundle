<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Facade;

use Northrook\Symfony\Core\DependencyInjection\Facade;
use Northrook\Symfony\Service\Toasts\Message;
use Northrook\Symfony\Service\Toasts\ToastService;

final class Toast extends Facade
{
    private static function manager() : ToastService {
        return Toast::getService( ToastService::class );
    }

    /**
     * @param string       $type  = ['info', 'success', 'warning', 'error', 'notice'][$any]
     * @param string       $message
     * @param null|string  $description
     * @param null|int     $timeoutMs
     *
     * @return Message
     */
    public static function message( string $type, string $message, ?string $description = null, ?int $timeoutMs = null,
    ) : Message {
        return Toast::manager()->message( $type, $message, $description, $timeoutMs );
    }

    /**
     * @param string       $message
     * @param null|string  $description
     * @param null|int     $timeoutMs
     *
     * @return Message
     */
    public static function info( string $message, ?string $description = null, ?int $timeoutMs = null,
    ) : Message {
        return Toast::manager()->message( 'info', $message, $description, $timeoutMs );
    }

    /**
     * @param string       $message
     * @param null|string  $description
     * @param null|int     $timeoutMs
     *
     * @return Message
     */
    public static function success( string $message, ?string $description = null, ?int $timeoutMs = null,
    ) : Message {
        return Toast::manager()->message( 'success', $message, $description, $timeoutMs );
    }

    /**
     * @param string       $message
     * @param null|string  $description
     * @param null|int     $timeoutMs
     *
     * @return Message
     */
    public static function warning( string $message, ?string $description = null, ?int $timeoutMs = null,
    ) : Message {
        return Toast::manager()->message( 'warning', $message, $description, $timeoutMs );
    }

    /**
     * @param string       $message
     * @param null|string  $description
     * @param null|int     $timeoutMs
     *
     * @return Message
     */
    public static function error( string $message, ?string $description = null, ?int $timeoutMs = null,
    ) : Message {
        return Toast::manager()->message( 'error', $message, $description, $timeoutMs );
    }

    /**
     * @param string       $message
     * @param null|string  $description
     * @param null|int     $timeoutMs
     *
     * @return Message
     */
    public static function notice( string $message, ?string $description = null, ?int $timeoutMs = null,
    ) : Message {
        return Toast::manager()->message( 'notice', $message, $description, $timeoutMs );
    }
}