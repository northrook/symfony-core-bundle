<?php

namespace Northrook\Symfony\Components;

use Northrook\Elements\Button as Button;
use Northrook\Elements\Element;
use Northrook\Elements\Icon;
use Northrook\Logger\Log\Timestamp;
use Northrook\Support\Format;

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
    public const  TAG     = 'toast';
    public const  CLASSES = 'notification';
    public const  TIMEOUT = 4500;

    private const ICONS = [
        'error'   => 'error:ui',
        'success' => 'success:ui',
        'warning' => 'warning:ui',
        'info'    => 'info:ui',
        'notice'  => 'notice:ui',
    ];

    private static array   $typeIcons   = [];
    private static ?string $closeButton = null;

    public function __construct(
        string  $type,
        string  $message,
        ?string $description = null,
        ?int    $timeout = null,
        array   $occurrences = [],
    ) {
        parent::__construct(
            class   : $type,
            timeout : $timeout ?? Notification::TIMEOUT,
            role    : 'listitem',
        );

        $timestamps = [];

        foreach ( $occurrences as $occurrence ) {
            [ $year, $time ] = explode( ' ', $occurrence->format( Timestamp::FORMAT_HUMAN ) );
            $timestamp = '<span class="year">' . $year . '</span><span class="time">' . $time . '</span>';
            $datetime  = $occurrence->format( DATE_W3C );


            if ( $occurrence->timestamp === time() ) {
                $timestamp .= ' <i>Now</i>';
            }

            $timestamps[] = '<time datetime="' . $datetime . '" role="listitem">' . $timestamp . '</time>';
        }

        $description = $description ? Format::markdown( $description ) : null;

        // if ( $description ) {
        //     $description = '<div class="description">' . $this->backtickCodeTags( $description ) . '</div>';
        // }

        $this->content = [
            Notification::$closeButton ??= Button::close(),
            Notification::getIcon( $type ),
            '<output class="message">' . $this->backtickCodeTags( $message ) . '</output>',
            $description,
            '<ol class="events">' . implode( '', array_reverse( $timestamps ) ) . '</ol>',
        ];
    }

    private function backtickCodeTags( string $string ) : string {
        return preg_replace( '/`(.+?)`/m', '<code>$1</code>', $string );
    }

    public static function setTypeIcons( array $typeIcons ) : void {
        Notification::$typeIcons = $typeIcons;
    }

    public static function setCloseButton( string $html ) : void {
        Notification::$closeButton = $html;
    }

    public static function getIcon( string $type ) : ?string {
        $get = array_merge( Notification::$typeIcons, Notification::ICONS );

        $icon = $get[ strtolower( $type ) ] ?? false;

        return $icon ? Icon::svg( $icon, 'icon' ) : null;
    }
}