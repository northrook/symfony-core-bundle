<?php

namespace Northrook\Symfony\Core\Services;

use JetBrains\PhpStorm\ExpectedValues;
use Northrook\Elements\Render\Template;
use Northrook\Symfony\Latte\Core as Latte;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\RawMessage;
use Throwable;
use Twig as Twig;

/**
 * @property Mailer    $mailer
 * @property Transport $transport
 */
class MailerService
{
    private const VALID_ELEMENTS = [
        'hr', 'br', 'p', 'img', 'div', 'a', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'b', 'i', 'u',
        'strong', 'em', 'small', 'blockquote',
    ];

    // FOR DEVELOPMENT
    private const MAILER_DSN = 'smtp://placeholder:PLuYThbZEZjHFJAHpWcs@mail.northrook.com:587';

    private readonly Mailer     $mailer;
    private readonly RawMessage $message;
    private ?string             $DSN       = null;
    private ?TransportInterface $transport = null;

    public function __construct(
        private readonly SettingsManagementService $settings,
        private readonly Twig\Environment          $twig,
        private readonly Latte\Environment         $latte,
    ) {
        // Set DSN from Settings if exists, else check $_ENV, if none; throw exception
    }

    public function __isset( string $name ) : bool {
        return isset( $this->$name );
    }

    public function __get( string $name ) : mixed {
        return match ( $name ) {
            'mailer'    => $this->mailer ?? new Mailer( $this->getTransport() ),
            'transport' => $this->transport ?? $this->getTransport(),
            'message'   => $this->message ?? new TemplatedEmail(),
            default     => null
        };
    }

    public function __set( string $name, $value ) : void {}

    /**
     * # Set SMTP DSN
     *
     * * `smtp://{$username}:{$password}@{$server}:{$port}`
     * * `smtp://username:password@smtp.example.com:587`
     *
     * @param string  $username
     * @param string  $password
     * @param string  $server
     * @param int     $port
     *
     * @return $this
     */
    public function SMTP(
        string $username,
        string $password,
        string $server,
        #[ExpectedValues( '25', '465', '587', '2525' )]
        int    $port = 587,
    ) : self {
        $dsn       = "smtp://{$username}:{$password}@{$server}:{$port}";
        $this->DSN = $dsn;

        return $this;
    }

    public function ready() : bool {
        return null !== $this->getDSN();
    }

    public function getDSN() : ?string {
        $this->DSN ??= $this->settings->MAILER_DSN ?? $_ENV[ 'MAILER_DSN' ] ?? self::MAILER_DSN;

        return $this->DSN;
    }

    /**
     * # Set DSN
     *
     * Provide a pre-constructed DSN string
     *  * `smtp://username:password@smtp.example.com:587`
     *
     * @param string  $DSN
     *
     * @return $this
     */
    public function setDSN( string $DSN ) : self {
        $this->DSN = $DSN;

        return $this;
    }

    /**
     * @return TransportInterface
     * @link https://symfony.com/doc/current/mailer.html#transport-setup Transport Setup
     */
    private function getTransport() : TransportInterface {
        return $this->transport ??= Transport::fromDsn( $this->getDSN() ?? '' );
    }

    private function mailer() : Mailer {
        return $this->mailer ??= new Mailer( $this->getTransport() );
    }
    //
    // public function message(
    //     string          $subject,
    //     array | Address $to,
    //     #[Language( 'Smarty' )]
    //     ?string         $template = null,
    //     array           $context = [],
    //     ?Address        $from = null,
    // ) : self {
    //
    //     $this->message = new TemplatedEmail();
    //     $from          ??= new Address(
    //         address : $this->settings->app( 'MAILER_FROM' ),
    //         name    : $this->settings->app( 'MAILER_NAME' ),
    //     );
    //
    //     $this->message->subject( $subject )
    //                   ->to( $to )
    //                   ->from( $from )
    //                   ->htmlTemplate( $template )
    //                   ->context( $context )
    //     ;
    //
    //     return $this;
    // }

    public function send(
        RawMessage $message,
        ?Envelope  $envelope = null,
    ) : array {

        if ( $message instanceof TemplatedEmail ) {
            $headers = $message->getHeaders();
            if ( !$headers->has( 'From' ) ) {
                $message->from(
                    new Address(
                        address : $this->settings->app( 'MAILER_FROM' ),
                        name    : $this->settings->app( 'MAILER_NAME' ),
                    ),
                );
            }

            $template = $message->getHtmlTemplate();
            $context  = $message->getContext();

            if ( $template ) {
                try {
                    if ( str_ends_with( $template, '.latte' ) ) {
                        $html = $this->latte->render( $template, $context, );
                    }
                    elseif ( str_ends_with( $template, '.twig' ) ) {
                        $html = $this->twig->render( $template, $context );
                    }
                    else {
                        $html = new Template( $template, $context );
                    }
                    // dd( $html->render(), $template, $context, $this );
                    $message->html( $html );
                }
                catch ( Throwable $exception ) {
                    return [
                        'sent'    => false,
                        'status'  => 'error',
                        'message' => $exception->getMessage(),
                    ];
                }
            }

        }

        $mailer = $this->mailer();

        try {
            $mailer->send( $message, $envelope );
            $status = [
                'sent'   => true,
                'status' => 'success',
            ];
        }
        catch ( TransportExceptionInterface $transportException ) {
            $status = [
                'sent'    => false,
                'status'  => 'error',
                'message' => $transportException->getMessage(),
            ];
        }

        dd(
            $mailer,
            $message,
            $envelope,
            $status,
            $this,
        );

        return $status;
    }
}