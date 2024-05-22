<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Latte;

use Northrook\Symfony\Core\Latte\Document\Theme;

final class Document
{


    private array $printed = [];

    private array $meta;

    private array $preload = [];


    public function __construct(
        string                $title,
        ?string               $description,
        ?string               $author,
        ?string               $keywords,
        string | array        $robots,
        public readonly Theme $theme,
        private array         $bodyAttributes,
        private array         $stylesheets,
        private array         $scripts,
    ) {

        $this->meta = [
            'content.title'       => $title,
            'content.description' => $description,
            'content.author'      => $author,
            'content.keywords'    => $keywords,
        ];


    }


}