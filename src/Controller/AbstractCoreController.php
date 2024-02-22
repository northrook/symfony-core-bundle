<?php

namespace Northrook\Symfony\Core\Controller;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

//abstract class AbstractCoreController implements ServiceSubscriberInterface
abstract class AbstractCoreController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
	protected ContainerInterface $container;


	#[Required]
	public function setContainer( ContainerInterface $container ) : ?ContainerInterface {
		$previous = $this->container ?? null;
		$this->container = $container;

		return $previous;
	}


}