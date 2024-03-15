<?php

namespace Northrook\Symfony\Core;

use Northrook\Symfony\Core\Services\CurrentRequestService;
use Symfony\Component\HttpFoundation as Http;

final class Request extends SymfonyCoreFacade
{
    public static function current() : CurrentRequestService {
        return self::getCurrentRequestService();
    }

    public static function currentRequest() : Http\Request {
        return self::getRequestStack()->getCurrentRequest();
    }

    public static function requestStack() : Http\RequestStack {
        return self::getRequestStack();
    }
}

function CurrentRequest() : Http\Request {
    return Request::currentRequest();
}