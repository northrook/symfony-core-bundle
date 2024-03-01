<?php

namespace Northrook\Symfony\Core\Services;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PathfinderService
{

	public function __construct(
		private ParameterBagInterface $parameter,
		private ?LoggerInterface $logger = null,
	) {}
}