<?php

namespace Northrook\Symfony\Core\Components;

use JetBrains\PhpStorm\ExpectedValues;
use Northrook\Elements\Button as Button;
use Northrook\Elements\Element;
use Northrook\Elements\Render\Template;
use Northrook\Logger\Log\Timestamp;

/**
 * ```
 * <toast {...attributes}>
 *     <button:close/>
 *     <output role="status">
 *         <i>{icon}</i>
 *         <span class="title">{title}</span>
 *         <div class="message">{message|nl2auto}</div>
 *     </output>
 * </toast>
 * ```
 * ---
 *
 */
class Notification extends Element
{
    private const TEMPLATE = '<span class="title">{title}</span><div class="message">{message|nl2auto}</div>';
    public const  TAG      = 'toast';
    public const
                  ERROR    = 'error',
                  WARNING  = 'warning',
                  INFO     = 'info',
                  SUCCESS  = 'success',
                  RANDOM   = 'random';

    private readonly string     $fingerprint;
    protected readonly Template $template;
    public string               $closeButton;
    public readonly Timestamp   $timestamp;


    public function __construct(
        #[ExpectedValues( flags : self::class )]
        public string     $status,
        public string     $title,
        public ?string    $message,
        protected ?string $icon, // this can only render from Assets when rendered by PHP. JS generated ones will use a preset for each status
    ) {

        $this->template    = new Template( Notification::TEMPLATE );
        $this->timestamp   = new Timestamp();
        $this->closeButton = Button::close();

        parent::__construct(
            [
                'status'  => $status,
                'class'   => "toast $status",
                'content' => $this->template,
            ],
        );
    }

    public function fingerprint() : string {
        return $this->fingerprint ??= crc32(
            strtolower(
                implode(
                    '',
                    [
                        $this->status,
                        $this->title,
                        $this->message,
                        $this->icon,
                    ],
                ),
            ),
        );
    }

    protected function onPrint() : void {

        $this->template->data = [
            'title'   => $this->title,
            'message' => $this->message,
        ];

        $this->content = [
            $this->closeButton,
            $this->icon,
            $this->template,
        ];
    }
}