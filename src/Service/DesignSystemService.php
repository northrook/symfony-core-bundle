<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Service;

use Northrook\DesignSystem;
use Psr\Log\LoggerInterface;
use function Northrook\normalizeKey;

final class DesignSystemService
{
    private array $designSystems = [];

    /**
     * @param ?LoggerInterface  $logger
     */
    public function __construct(
        private readonly ?LoggerInterface $logger,
    ) {}

    public function system( string $get ) : DesignSystem {
        $get = normalizeKey( $get );
        if ( 'admin' === $get ) {
            throw new \InvalidArgumentException(
                "The Design System 'admin' key is reserved.",
            );
        }
        return $this->designSystems[ $get ] ??= new DesignSystem();
    }

    public function admin() : DesignSystem {
        if ( isset( $this->designSystems[ 'admin' ] ) ) {
            return $this->designSystems[ 'admin' ];
        }

        $admin = new DesignSystem();

        $admin->colorPalette
            ->addPalette(
                name   : 'baseline',
                from   : [ 222, 9 ],
                method : [
                             2, 5, 8,
                             12, 60, 55,
                             92, 95, 98,
                         ],
            )
            ->addPalette( 'primary', [ 222, 100, 50 ] )
            ->systemPalettes();

        return $this->designSystems[ 'admin' ] = $admin;
    }
}