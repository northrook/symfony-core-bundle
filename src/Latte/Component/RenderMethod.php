<?php

namespace Northrook\Symfony\Core\Latte\Component;

enum RenderMethod : string
{
    /** Render value and choices as static HTML */
    case STATIC = 'static';
    /** Fetch value and choices at PHP runtime */
    case RUNTIME = 'runtime';
    /** Fetch value and choices in the frontend via AJAX */
    case LIVE = 'live';
}