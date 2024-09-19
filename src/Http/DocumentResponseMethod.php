<?php

namespace Northrook\Symfony\Core\Http;

use Northrook\Symfony\Core\DependencyInjection\ServiceContainer;
use Northrook\Symfony\Service\Document\DocumentService;


trait DocumentResponseMethod
{
    
    // :: This will force the Public and Admin controllers to consider what their document looks like.
    //    Like adding in Menu, setting global robots etc.

    abstract protected function documentResponse() : DocumentResponse;

    final protected function documentService() : DocumentService
    {
        return ServiceContainer::get( DocumentService::class );
    }
}