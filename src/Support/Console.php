<?php

namespace Northrook\Symfony\Core\Support;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;

final class Console
{

    private function __construct() {}

    public static function info(
        string ...$messages
    ) : string {

        $output = new OutputFormatterStyle();
        $output->setBackground( 'cyan' );
        $output->setForeground( 'black' );
        $output->setOption( 'bold' );
        $print[] = $output->apply( ' [ INFO ] ' );
        foreach ( $messages as $message ) {
            $print[] = " $message";
        }

        return implode( PHP_EOL, $print ) . PHP_EOL;
    }

    public static function OK(
        string ...$messages
    ) : string {

        $output = new OutputFormatterStyle();
        $output->setBackground( 'green' );
        $output->setForeground( 'black' );
        $output->setOption( 'bold' );
        $print[] = $output->apply( ' [ OK ] ' );
        foreach ( $messages as $message ) {
            $print[] = " $message";
        }

        return implode( PHP_EOL, $print ) . PHP_EOL;
    }

    public static function error(
        string ...$messages
    ) : string {

        $output = new OutputFormatterStyle();
        $output->setBackground( 'red' );
        $output->setForeground( 'black' );
        $output->setOption( 'bold' );
        $print[] = $output->apply( ' [ ERROR ] ' );
        foreach ( $messages as $message ) {
            $print[] = " $message";
        }

        return implode( PHP_EOL, $print ) . PHP_EOL;
    }
}