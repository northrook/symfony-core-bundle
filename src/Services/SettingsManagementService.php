<?php

namespace Northrook\Symfony\Core\Services;

use Northrook\Symfony\Core\App;

/**
 * @property string $MAILER_DSN
 */
final class SettingsManagementService
{

    public const APP = [
        'NAME'        => null,
        'DEBUG'       => null,
        'MAILER_DSN'  => null,
        'MAILER_FROM' => null,
        'MAILER_NAME' => null,
        'MAILER_LANG' => null,
        'HOME_URL'    => null,
    ];

    private const ADMIN  = [

    ];

    private const PUBLIC = [

    ];

    private array $warnings = [];


    private readonly array $public;
    private readonly array $admin;
    private readonly array $app;
    private readonly array $settings;

    public function __construct() {}

    public function public( ?string $name = null ) : mixed {
        $this->public ??= array_merge( self::PUBLIC, $this->public );
        return $name === null ? $this->public : $this->public[ $name ] ?? null;
    }

    public function admin( ?string $name = null ) : mixed {
        $this->admin ??= array_merge( self::ADMIN, $this->admin );
        return $name === null ? $this->admin : $this->admin[ $name ] ?? null;
    }

    /**
     * @param null|string  $name  = ['DEBUG','MAILER_DSN','MAILER_FROM','MAILER_NAME','MAILER_LANG','HOME_URL'][$any]
     *
     * @return mixed
     */
    public function app( ?string $name = null ) : mixed {

        if ( !isset( $this->app ) ) {


            $app = array_merge(
                self::APP,
            // get from database and .env firstly
            );

            $app[ 'LOCALE' ]      ??= 'en'; //TODO : This is a placeholder
            $app[ 'DEBUG' ]       ??= App::env( 'debug' );
            $app[ 'NAME' ]        ??= $_ENV[ 'APP_NAME' ] ?? 'symfony-core'; // TODO : This is a placeholder
            $app[ 'HOME_URL' ]    ??= $_ENV[ 'HOME_URL' ] ?? 'https://example.com';
            $app[ 'MAILER_DSN' ]  = $_ENV[ 'MAILER_DSN' ] ?? null;
            $app[ 'MAILER_FROM' ] = $_ENV[ 'MAILER_FROM' ] ?? 'placeholder@northrook.com';
            $app[ 'MAILER_NAME' ] = $_ENV[ 'MAILER_NAME' ] ?? 'Placeholder';

            if ( $app[ 'MAILER_FROM' ] === null ) {
                $app[ 'MAILER_FROM' ] = 'no-reply@' . trim( strstr( $app[ 'HOME_URL' ], '//' ), " \n\r\t\v\0/", );
                $this->warnings[]     =
                    'MAILER_FROM not set, generating from APP.HOME_URL. This is not recommended. Set MAILER_FROM in .env or override in Settings.';
            }

            if ( $app[ 'MAILER_NAME' ] === null ) {
                $app[ 'MAILER_NAME' ] = $app[ 'NAME' ];
                $this->warnings[]     =
                    'MAILER_NAME not set, using APP.NAME. This is not recommended. Set MAILER_NAME in .env or override in Settings.';
            }

            $this->app = $app;
        }

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