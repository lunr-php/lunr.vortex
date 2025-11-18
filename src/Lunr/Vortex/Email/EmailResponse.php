<?php

/**
 * This file contains an abstraction for the response from the Email service.
 *
 * SPDX-FileCopyrightText: Copyright 2014 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\Email;

use Lunr\Vortex\PushNotificationResponseInterface;
use Lunr\Vortex\PushNotificationStatus;
use Psr\Log\LoggerInterface;

/**
 * Email notification response wrapper.
 *
 * @phpstan-type MailResults array<
 *     string,
 *     array{
 *         is_error: bool,
 *         error_message: string
 *     }
 * >
 */
class EmailResponse implements PushNotificationResponseInterface
{

    /**
     * Push notification statuses per endpoint.
     * @var array<string, PushNotificationStatus>
     */
    private array $statuses;

    /**
     * Shared instance of a Logger class.
     * @var LoggerInterface
     */
    private readonly LoggerInterface $logger;

    /**
     * Raw payload that was sent out.
     * @var string
     */
    protected readonly string $payload;

    /**
     * Constructor.
     *
     * @param MailResults     $mailResults Contains endpoints with corresponding PHPMailer results.
     * @param LoggerInterface $logger      Shared instance of a Logger.
     * @param string          $payload     Raw payload that was sent out.
     */
    public function __construct(array $mailResults, LoggerInterface $logger, string $payload)
    {
        $this->logger   = $logger;
        $this->statuses = [];
        $this->payload  = $payload;

        $this->handle_sent_notifications($mailResults);
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->statuses);
    }

    /**
     * Get notification delivery status for an endpoint.
     *
     * @param string $endpoint Endpoint
     *
     * @return PushNotificationStatus Delivery status for the endpoint
     */
    public function get_status(string $endpoint): PushNotificationStatus
    {
        if (!array_key_exists($endpoint, $this->statuses))
        {
            return PushNotificationStatus::Unknown;
        }

        return $this->statuses[$endpoint];
    }

    /**
     * Store the results per endpoint in the statuses property
     *
     * @param MailResults $mailResults Array containing is_error and a possible error message per endpoint
     *
     * @return void
     */
    private function handle_sent_notifications(array $mailResults): void
    {
        foreach ($mailResults as $endpoint => $resultArray)
        {
            if ($resultArray['is_error'] === FALSE)
            {
                $this->statuses[$endpoint] = PushNotificationStatus::Success;
            }
            else
            {
                $this->statuses[$endpoint] = PushNotificationStatus::Error;

                $context = [ 'endpoint' => $endpoint, 'message' => $resultArray['error_message'] ];

                $this->logger->warning('Sending email notification to {endpoint} failed: {message}', $context);
            }
        }
    }

}

?>
