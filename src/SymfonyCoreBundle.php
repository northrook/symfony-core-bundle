<?php

namespace Northrook\Symfony\Core;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SymfonyCoreBundle extends Bundle
{
	public function getPath() : string {
		return dirname( __DIR__ );
	}
}