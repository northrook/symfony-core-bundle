<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Latte\Document;

final readonly class Script implements \Stringable
{

    public function __construct(
        public ?string $src,
        public string  $type = 'text/javascript',
        public bool    $async = false,
        public bool    $blocking = false,
        public bool    $defer = false,
        public string  $fetchpriority,             // 'high', 'low', 'auto'
        public string  $crossorigin = 'anonymous', // 'anonymous', 'use-credentials'
        public string  $integrity,
        public string  $nonce,
        public string  $referrerpolicy = 'no-referrer',
        // 'no-referrer', 'no-referrer-when-downgrade', 'origin', 'origin-when-cross-origin', 'same-origin', 'strict-origin', 'strict-origin-when-cross-origin', 'unsafe-url'
        bool           $isInline = false, // If true, load src content as inline script, if file read, else treat as raw script string


    ) {}

    public function __toString() : string {
        // TODO: Implement __toString() method.
    }
}