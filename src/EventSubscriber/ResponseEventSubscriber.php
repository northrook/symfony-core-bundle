<?php

namespace Northrook\Symfony\Core\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;

final class ResponseEventSubscriber implements EventSubscriberInterface
{

    private bool    $isMainRequest = false;
    private Request $request;

    public function request( RequestEvent $request ) : void {
        // echo '<pre>' . __METHOD__ . '</pre>' . PHP_EOL;

        $this->isMainRequest = $request->isMainRequest();
        $this->request       = $request->getRequest();
    }

    public function controller( ControllerEvent $controller ) : void {
        // echo '<pre>' . __METHOD__ . '</pre>' . PHP_EOL;
    }

    public function controllerArguments( ControllerArgumentsEvent $event ) : void {
        // echo '<pre>' . __METHOD__ . '</pre>' . PHP_EOL;
    }

    public function view( ViewEvent $view ) : void {
        // echo '<pre>' . __METHOD__ . '</pre>' . PHP_EOL;
    }

    public function response( ResponseEvent $response ) : void {
        // echo '<pre>' . __METHOD__ . '</pre>' . PHP_EOL;
    }

    public function flashMessages( FinishRequestEvent $response ) : void {
        // echo '<pre>' . __METHOD__ . '</pre>' . PHP_EOL;
    }

    public function terminate( TerminateEvent $terminate ) : void {
        // echo '<pre>' . __METHOD__ . '</pre>' . PHP_EOL;
    }

    public function exception( ExceptionEvent $exception ) : void {
        // echo '<pre>' . __METHOD__ . '</pre>' . PHP_EOL;
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