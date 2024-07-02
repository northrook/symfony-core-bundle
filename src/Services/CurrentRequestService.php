<?php

namespace Northrook\Symfony\Core\Services;

use JetBrains\PhpStorm\Deprecated;
use Northrook\Logger\Log\Level;
use Northrook\Logger\Log\Timestamp;

#[Deprecated]
readonly class CurrentRequestService
{

    /**
     * @param string | Level  $type  = ['error', 'warning', 'info', 'success'][$any]
     * @param string          $message
     * @param null|string     $description
     * @param null|int        $timeoutMs
     * @param bool            $log
     *
     * @return void
     */
    public function addFlash(
        string | Level $type,
        string         $message,
        ?string        $description = null,
        ?int           $timeoutMs = null,
        bool           $log = false,
    ) : void {

        if ( $type instanceof Level ) {
            $level = $type->name;
        }
        else {
            $level = in_array( ucfirst( $type ), Level::NAMES ) ? ucfirst( $type ) : 'Info';
        }


        if ( $log ) {
            $this?->logger->log( $level, $message );
        }

        $this->flashBag()->add(
            $type,
            [
                'level'       => $level,
                'message'     => $message,
                'description' => $description,
                'timeout'     => $timeoutMs,
                'timestamp'   => new Timestamp(),
            ],
        );
    }
}