<?php

namespace Northrook\Symfony\Core\Components;

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
    public const  TAG = 'toast';

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
        int     $timeout = 1200,
        array   $occurrences = [],
    ) {
        parent::__construct(
            class   : "notification $type",
            timeout : $timeout,
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


        $this->content = [
            Notification::$closeButton ??= Button::close(),
            Notification::getIcon( $type ),
            '<output class="message">' . $message . '</output>',
            $description ? Format::nl2Auto( $description ) : null,
            '<ol>' . implode( '', array_reverse( $timestamps ) ) . '</ol>',
        ];
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

        return $icon ? Icon::svg( $icon ) : null;
    }
}