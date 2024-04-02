<?php

namespace Northrook\Symfony\Core\Services;

/**
 * @property string $MAILER_DSN
 */
class SettingsManagementService
{

    private const APP = [
        'MAILER_DSN' => null,
    ];

    private const ADMIN  = [

    ];
    
    private const PUBLIC = [

    ];


    private array $public   = [];
    private array $admin    = [];
    private array $app      = [];
    private array $settings = [];

    public function __construct() {}

    public function public( ?string $name = null ) : mixed {
        $this->public ??= array_merge( self::PUBLIC, $this->public );
        return $name === null ? $this->public : $this->public[ $name ] ?? null;
    }

    public function admin( ?string $name = null ) : mixed {
        $this->admin ??= array_merge( self::ADMIN, $this->admin );
        return $name === null ? $this->admin : $this->admin[ $name ] ?? null;
    }

    public function app( ?string $name = null ) : mixed {
        $this->app ??= array_merge( self::APP, $this->app );
        return $name === null ? $this->app : $this->app[ $name ] ?? null;
    }

    public function settings( ?string $name = null ) : mixed {
        $this->settings ??= array_merge( $this->public, $this->admin, $this->app );
        return $name === null ? $this->settings : $this->settings[ $name ] ?? null;
    }

    public function __get(
        string $name,
    ) : mixed {
        return match ( $name ) {
            'MAILER_DSN' => $this->app( 'MAILER_DSN' ),
            default      => $this->settings( $name ),
        };
    }


    public function __set(
        string $name, $value,
    ) : void {
        return;
    }

    public function __isset(
        string $name,
    ) : bool {
        return isset( $this->settings[ $name ] );
    }
}