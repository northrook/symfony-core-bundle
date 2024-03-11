<?php

namespace Northrook\Symfony\Core\Latte;

use Latte;

trait LatteTemplateLoaderTrait
{
	protected function latteTemplate( string $path ) : string {

		$engine = new Latte\Engine();

		return $engine::class;
	}
}