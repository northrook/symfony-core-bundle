<?php

namespace Northrook\Symfony\Core\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ResponseEventSubscriber implements EventSubscriberInterface
{
    public function request() : void {
        echo '<pre>' . __METHOD__ . '</pre>';
    }

    public function controller() : void {
        echo '<pre>' . __METHOD__ . '</pre>';
    }

    public function controllerArguments() : void {
        echo '<pre>' . __METHOD__ . '</pre>';
    }

    public function view() : void {
        echo '<pre>' . __METHOD__ . '</pre>';
    }

    public function response() : void {
        echo '<pre>' . __METHOD__ . '</pre>';
    }

    public function flashMessages() : void {
        echo '<pre>' . __METHOD__ . '</pre>';
    }

    public function terminate() : void {
        echo '<pre>' . __METHOD__ . '</pre>';
    }

    public function exception() : void {
        echo '<pre>' . __METHOD__ . '</pre>';
    }

    public static function getSubscribedEvents() : array {
        return [
            'kernel.request'              => 'request',
            'kernel.controller'           => 'controller',
            'kernel.controller_arguments' => 'controllerArguments',
            'kernel.view'                 => 'view',
            'kernel.response'             => 'response',
            'kernel.finish_request'       => 'flashMessages',
            'kernel.terminate'            => 'terminate',
            'kernel.exception'            => 'exception',
        ];
    }
}