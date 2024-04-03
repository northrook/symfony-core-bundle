<?php

namespace Northrook\Symfony\Core\Services;

use JetBrains\PhpStorm\ExpectedValues;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\RawMessage;
use Twig\Environment;

class MailerService
{
    // FOR DEVELOPMENT
    private const MAILER_DSN = 'smtp://f624a425533ae5:82657c57465a24@sandbox.smtp.mailtrap.io:2525';

    private readonly Mailer     $mailer;
    private ?string             $DSN       = null;
    private ?TransportInterface $transport = null;

    public function __construct(
        private readonly SettingsManagementService $settings,
        private readonly Environment               $twig,
    ) {
        // Set DSN from Settings if exists, else check $_ENV, if none; throw exception
    }

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

            try {
                $html = $this->twig->render(
                    $message->getHtmlTemplate(),
                    $message->getContext(),
                );
            }
            catch ( \Throwable $exception ) {
                return [
                    'sent'    => false,
                    'status'  => 'error',
                    'message' => $exception->getMessage(),
                ];
            }

            $message->html( $html );
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