<?php

namespace Northrook\Symfony\Core\Components;

use Northrook\Symfony\Core\Latte\Component;

/**
 * # Button
 *
 * * Renders `<button type="auto" ... >[content]</button>`
 *
 * Attributes:
 * * `name` - The name of the button
 * * `value` - The value of the button
 * * `type` - The type of button. Defaults to `button` unless set
 * * `class` - The class of the button
 * * `content` - innerHTML of the button
 * * `icon` - Renders an icon as innerHTML
 * * `disabled` - Whether the button is disabled
 *
 * InnerHTML templating:
 * * `{icon}` - Renders an icon if set
 * * `{content}` - Renders innerHTML if set
 *
 * ```
 * // example.latte
 * <button icon="close:ui" content="{icon} Close something"/>
 * // rendered html
 * <button type="button" class="icon"><i><svg/></i>Close something</button>
 * ```
 *
 */
class Button extends Component
{

    public function build() : string {
        return '<button type="submit" class="btn btn-primary">Submit</button>';
    }
}