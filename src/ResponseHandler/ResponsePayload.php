<?php

namespace Northrook\Symfony\Core\ResponseHandler;

/**
 * @internal
 */
final readonly class ResponsePayload implements \Stringable
{

    public function __construct(
            public ?string        $content,
            public array | object $parameters = [],
    ) {}

    public function __toString() : string
    {
        // TODO: Implement __toString() method.
    }
}