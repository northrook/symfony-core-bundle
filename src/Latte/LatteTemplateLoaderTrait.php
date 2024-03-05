<?php

namespace Northrook\Symfony\Core\Latte;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use Latte;

trait LatteTemplateLoaderTrait
{
	protected function latteTemplate( string $path ) : string {

		$engine = new Latte\Engine();

		return $engine::class;

	}
}