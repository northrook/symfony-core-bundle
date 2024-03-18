<?php

/**
 * This file is part of the Northrook Core Symfony Bundle.
 *
 * Contains mimetypes for simple .extension lookup.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Common_types
 */

return [

    // Text and XML
    'txt'    => 'text/plain',
    'htm'    => 'text/html',
    'html'   => 'text/html',
    'php'    => 'text/html',
    'css'    => 'text/css',
    'js'     => 'application/javascript',

    // Documents
    'rtf'    => 'application/rtf',
    'doc'    => 'application/msword',
    'pdf'    => 'application/pdf',
    'eps'    => 'application/postscript',

    // Data sources
    'csv'    => 'text/csv',
    'json'   => 'application/json',
    'jsonld' => 'application/ld+json',
    'xls'    => 'application/vnd.ms-excel',
    'xml'    => 'application/xml',

    // Images and vector graphics
    'apng'   => 'image/png',
    'png'    => 'image/png',
    'jpe'    => 'image/jpeg',
    'jpeg'   => 'image/jpeg',
    'jpg'    => 'image/jpeg',
    'gif'    => 'image/gif',
    'bmp'    => 'image/bmp',
    'ico'    => 'image/vnd.microsoft.icon',
    'tiff'   => 'image/tiff',
    'tif'    => 'image/tiff',
    'svg'    => 'image/svg+xml',
    'svgz'   => 'image/svg+xml',
    'webp'   => 'image/webp',
    'webm'   => 'video/webm',

    // archives
    '7z'     => 'application/x-7z-compressed',
    'zip'    => 'application/zip',
    'rar'    => 'application/x-rar-compressed',
    'exe'    => 'application/x-msdownload',
    'msi'    => 'application/x-msdownload',
    'cab'    => 'application/vnd.ms-cab-compressed',
    'tar'    => 'application/x-tar',

    // audio/video
    'mp3'    => 'audio/mpeg',
    'qt'     => 'video/quicktime',
    'mov'    => 'video/quicktime',

    // Fonts
    'ttf'    => 'font/ttf',
    'otf'    => 'font/otf',
    'woff'   => 'font/woff',
    'woff2'  => 'font/woff2',
    'eot'    => 'application/vnd.ms-fontobject',

];