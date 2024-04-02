<?php

namespace Northrook\Symfony\Core\Services;

use JetBrains\PhpStorm\ExpectedValues;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;

class MailerService
{
    // FOR DEVELOPMENT
    private const MAILER_DSN = 'smtp://f624a425533ae5:82657c57465a24@sandbox.smtp.mailtrap.io:2525';

    private ?string             $DSN       = null;
    private ?TransportInterface $transport = null;

    public function __construct(
        private readonly SettingsManagementService $settings,
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

    private function getMailer() : Mailer {
        return new Mailer(
            transport : $this->getTransport(),
        );
    }

    public function send(
        RawMessage $message,
        ?Envelope  $envelope = null,
    ) : array {
        try {
            $this->getMailer()->send( $message, $envelope );
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

        return $status;
    }
}