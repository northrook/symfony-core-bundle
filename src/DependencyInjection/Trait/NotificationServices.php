<?php

namespace Northrook\Symfony\Core\DependencyInjection\Trait;

use JetBrains\PhpStorm\Deprecated;
use Northrook\Symfony\Core\DependencyInjection\CoreDependencies;

#[Deprecated]
trait NotificationServices
{
    protected readonly CoreDependencies $get;

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
        $this->get->request->addFlash( $type, $message, $description, $timeoutMs, $log );
    }
}