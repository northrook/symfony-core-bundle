<?php

namespace Northrook\Symfony\Core\Components\Input;

use Northrook\Types as Type;
use Northrook\Symfony\Core\Components\Input;

class Password extends Input
{
	private readonly Type\Password $password;
}