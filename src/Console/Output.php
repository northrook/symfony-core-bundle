<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Console;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;


/**
 * Provides a simple output formatter.
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
final class Output
{

    private function __construct() {}

    private static function print(
        string $output,
        array  $messages,
    ) : string {
        array_walk(
            $messages,
            static function ( $message ) use ( &$print ) {
                $print[] = " $message";
            },
        );

        return $output . implode( PHP_EOL, $print ) . PHP_EOL . PHP_EOL;
    }

    public static function info(
        string ...$messages
    ) : string {

        $output = new OutputFormatterStyle();
        $output->setBackground( 'cyan' );
        $output->setForeground( 'black' );
        $output->setOption( 'bold' );

        return Output::print( $output->apply( ' [ INFO ] ' ), $messages );
    }

    public static function OK(
        string ...$messages
    ) : string {

        $output = new OutputFormatterStyle();
        $output->setBackground( 'green' );
        $output->setForeground( 'black' );
        $output->setOption( 'bold' );

        return Output::print( $output->apply( ' [ OK ] ' ), $messages );
    }

    public static function error(
        string ...$messages
    ) : string {

        $output = new OutputFormatterStyle();
        $output->setBackground( 'red' );
        $output->setForeground( 'black' );
        $output->setOption( 'bold' );

        return Output::print( $output->apply( ' [ ERROR ] ' ), $messages );
    }
}