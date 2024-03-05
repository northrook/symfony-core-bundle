<?php

namespace Northrook\Symfony\Core\DataCollector;

use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RequestCollector extends AbstractDataCollector
{
	public function collect( Request $request, Response $response, Throwable $exception = null ) : void {
		$this->data = [
			'method'                   => $request->getMethod(),
			'acceptable_content_types' => $request->getAcceptableContentTypes(),
		];
		dd( $this );
	}
}