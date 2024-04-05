<?php

namespace Northrook\Symfony\Core\Latte;

use Northrook\Support\Get;
use Psr\Log\LoggerInterface;
use Stringable;
use Symfony\Component\Stopwatch\Stopwatch;

abstract class Component implements Stringable
{
    protected const TAG = 'field';

    public readonly string $string;

    public function __construct(
        protected readonly ?LoggerInterface $logger = null,
        protected readonly ?Stopwatch       $stopwatch = null,
    ) {
        $this->stopwatch->start( Get::className(), 'Component' );
        $this->construct();
    }

    protected function construct() : void {}

    abstract public function build() : string;

    final protected function renderComponentString() : string {
        $this->string ??= $this->build();
        $this->stopwatch->stop( Get::className() );
        return $this->string;
    }

    final public function print( bool $pretty = false ) : string {
        return $this->renderComponentString();
    }

    final public function __toString() : string {
        return $this->renderComponentString();
    }
}