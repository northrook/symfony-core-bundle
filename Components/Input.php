<?php

namespace Northrook\Symfony\Components;

class Input extends Component
{
    protected const TAG     = 'field';
    protected const CLASSES = [ 'input', 'field' ];

    public function build() : void {}

    protected function construct() : void {

        // $this->component->

        $this->component->content = [
            'test' => 'value',
        ];
    }

    protected function template() : string {
        // language=Smarty
        return <<<HTML
            {input}
            {label}
        HTML;
    }
}