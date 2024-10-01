<?php

namespace PHPSTORM_META {

    override(
        \Northrook\Settings::get( 0 ),
        map(
            [
                'security.registration'       => 'true',
                'security.password_reset'     => 'bool',
                'security.email_verification' => 'bool',
                'security.admin'              => 'bool',
                'url.trailingSlash'           => 'bool',
                'url.enforceHttps'            => 'bool',
                'url.log.insecureUrl'         => 'bool',
                'url.log.404'                 => 'bool',
            ],
        ),
    );

}