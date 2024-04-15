<?php

namespace Northrook\Symfony\Core\Form;

use Northrook\Elements\Element\Attribute;
use Northrook\Elements\Icon;
use Northrook\Elements\Render\Template;
use Northrook\Support\Format;
use Northrook\Support\Str;
use Stringable;

class Error implements Stringable
{

    public const ERROR = 'error', WARNING = 'warning', INFO = 'info';

    private const TEMPLATE = <<<HTML
        <div class="{class}" type="{type}">
            {icon}
            {message}
        </div>
    HTML;

    public readonly string  $message;
    public readonly ?string $icon;

    public function __construct(
        public readonly string  $fieldName,
        string                  $message,
        ?string                 $icon = null,
        public readonly string  $type = self::ERROR,
        private readonly string $template = self::TEMPLATE,
        private array           $data = [],
    ) {
        $this->message = Format::nl2Auto( Str::squish( $message ) );
        $this->icon    = $icon ? Icon::svg( $icon ) : null;

        $classes = Attribute::classes( [ 'message', $this->type, $data[ 'class' ] ?? null ] );
        unset( $data[ 'class' ] );

        $this->data = array_merge(
            $this->data,
            [
                'fieldName' => $fieldName,
                'message'   => $this->message,
                'icon'      => $this->icon,
                'type'      => $this->type,
                'class'     => $classes,
            ],
        );
    }

    public function __toString() : string {
        return ( new Template( $this->template, $this->data ) )->render();
    }
}