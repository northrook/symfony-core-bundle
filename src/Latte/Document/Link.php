<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Latte\Document;

final class Link implements \Stringable
{

    public function __construct(
        public ?string $href,
        public string  $rel = 'stylesheet',
    ) {}

    public function __toString() : string {
        // TODO: Implement __toString() method.
    }
}