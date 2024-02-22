<?php

namespace Northrook\Symfony\Core\Enums;

enum Env: string {
case PRODUCTION = 'prod';
case DEV        = 'dev';
case DEBUG      = 'debug';
}