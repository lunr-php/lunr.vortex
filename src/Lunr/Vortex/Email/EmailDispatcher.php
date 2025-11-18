<?php

/**
 * This file contains functionality to dispatch Email Notifications.
 *
 * SPDX-FileCopyrightText: Copyright 2014 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\Email;

use InvalidArgumentException;
use Lunr\Vortex\PushNotificationMultiDispatcherInterface;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;

/**
 * Email Notification Dispatcher.
 */
class EmailDispatcher implements PushNotificationMultiDispatcherInterface
{
    /**
     * Email Notification source.
     * @var string
     */
    private string $source;

    /**
     * Shared instance of the mail transport class.
     *
     * @var PHPMailer
     */
    private readonly PHPMailer $mailTransport;

    /**
     * Shared instance of a Logger class.
     *
     * @var LoggerInterface
     */
    private readonly LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param PHPMailer       $mailTransport Shared instance of the mail transport class.
     * @param LoggerInterface $logger        Shared instance of a Logger.
     */
    public function __construct(PHPMailer $mailTransport, LoggerInterface $logger)
    {
        $this->source = '';
        $this->logger = $logger;

        $this->mailTransport = $mailTransport;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->source);
    }

    /**
     * Get a cloned instance of the mail transport class.
     *
     * @return PHPMailer Cloned instance of the PHPMailer class
     */
    public function clone_mail(): PHPMailer
    {
        return clone $this->mailTransport;
    }

    /**
     * Send the notification.
     *
     * @param object   $payload   Payload object
     * @param string[] $endpoints Endpoints to send to in this batch
     *
     * @return EmailResponse Response object
     */
    public function push(object $payload, array &$endpoints): EmailResponse
    {
        if (!$payload instanceof EmailPayload)
        {
            throw new InvalidArgumentException('Invalid payload object!');
        }

        $payloadArray = $payload->get_payload();

        // PHPMailer is not reentrant, so we have to clone it before we can do endpoint specific configuration.
        $mailTransport = $this->clone_mail();
        $mailTransport->setFrom($this->source);

        $mailTransport->Subject  = $payloadArray['subject']; // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
        $mailTransport->Body     = $payloadArray['body']; // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
        $mailTransport->CharSet  = $payloadArray['charset']; // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
        $mailTransport->Encoding = $payloadArray['encoding']; // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName

        $mailTransport->isHTML($payloadArray['body_as_html']);

        $mailResults = [];

        foreach ($endpoints as $endpoint)
        {
            try
            {
                $mailTransport->addAddress($endpoint);

                $mailTransport->send();

                $mailResults[$endpoint] = [
                    'is_error'      => $mailTransport->isError(),
                    'error_message' => $mailTransport->ErrorInfo, // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
                ];
            }
            catch (PHPMailerException $e)
            {
                $mailResults[$endpoint] = [
                    'is_error'      => $mailTransport->isError(),
                    'error_message' => $mailTransport->ErrorInfo, // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
                ];
            }
            finally
            {
                $mailTransport->clearAddresses();
            }
        }

        return new EmailResponse($mailResults, $this->logger, $mailTransport->getSentMIMEMessage());
    }

    /**
     * Set the source for the email.
     *
     * @param string $source The endpoint for the email
     *
     * @return EmailDispatcher Self reference
     */
    public function set_source(string $source): self
    {
        $this->source = $source;

        return $this;
    }

}

?>
