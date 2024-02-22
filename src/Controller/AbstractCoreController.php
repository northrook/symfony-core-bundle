<?php

namespace Northrook\Symfony\Core\Controller;

use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;

abstract class AbstractCoreController extends AbstractController
{
	protected ContainerInterface $container;


	#[Required]
	public function setContainer( ContainerInterface $container ) : ?ContainerInterface {
		$previous = $this->container ?? null;
		$this->container = $container;

		return $previous;
	}

	public static function getSubscribedServices(): array
	{
		return [
			'router' => '?'.RouterInterface::class,
			'request_stack' => '?'.RequestStack::class,
			'http_kernel' => '?'.HttpKernelInterface::class,
			'serializer' => '?'.SerializerInterface::class,
			'security.authorization_checker' => '?'.AuthorizationCheckerInterface::class,
			'twig' => '?'.Environment::class,
			'form.factory' => '?'.FormFactoryInterface::class,
			'security.token_storage' => '?'.TokenStorageInterface::class,
			'security.csrf.token_manager' => '?'.CsrfTokenManagerInterface::class,
			'parameter_bag' => '?'.ContainerBagInterface::class,
			'web_link.http_header_serializer' => '?'.HttpHeaderSerializer::class,
		];
	}


}