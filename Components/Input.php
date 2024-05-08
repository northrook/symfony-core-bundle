<?php

namespace Northrook\Symfony\Components;

class Input extends Component
{
    protected const TAG = 'field';

    public function build() : ?string {
        return $this->className;
    }

    protected function construct() : void {
        $this->component = [
            'test' => 'value',
        ];
    }
}