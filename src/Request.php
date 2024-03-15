<?php

namespace Northrook\Symfony\Core;

use Northrook\Symfony\Core\Services\CurrentRequestService;

final class Request extends CurrentRequestService
{
    public static function get() : CurrentRequestService {
        return parent::getCurrentRequestService();
    }
}