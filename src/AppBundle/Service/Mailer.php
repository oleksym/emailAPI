<?php

namespace AppBundle\Service;

use Psr\Log\LoggerInterface;
use AppBundle\Exception\UnknownMailerClientException;

class Mailer
{
    private $logger;
    private $swift_mailer;
    private $mailer_client = null;

    public function __construct(LoggerInterface $logger, $swift_mailer)
    {
        $this->logger = $logger;
        $this->swift_mailer = $swift_mailer;
    }

    public function setMailerClient($client)
    {
        if (!is_string($client)) {
            throw new UnknownMailerClientException('Unknown Mailer Client '.$client);
        }

        switch ($client) {
            case 'smtp':
                $this->mailer_client = $this->swift_mailer;
                break;
            case 'rest':
                $this->mailer_client = new \AppBundle\Lib\RESTMailTransporter\RESTMailTransporter();
                break;
            default:
                throw new UnknownMailerClientException('Unknown Mailer Client '.$client);
                break;
        }

        return $this;
    }

    public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
    {
        try {
            $this->mailer_client->send($message, $failedRecipients);
            $this->logger->info('API.Mailer: Email sent');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
