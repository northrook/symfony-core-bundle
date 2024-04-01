<?php

namespace Northrook\Symfony\Core;

use Symfony\Component\HttpFoundation\Response;

final class Latte extends SymfonyCoreFacade
{

    public static function response() : Response {
        return new Response();
    }

    public static function render(
        string         $template,
        object | array $parameters = [],
    ) : ?string {
        return self::getLatteEnvironment()?->render(
            template   : $template,
            parameters : $parameters,
        );
    }

}