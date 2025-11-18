<?php

/**
 * This file contains the JPushBatchResponse class.
 *
 * SPDX-FileCopyrightText: Copyright 2020 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush;

use Lunr\Vortex\PushNotificationStatus;
use Psr\Log\LoggerInterface;
use WpOrg\Requests\Response;
use WpOrg\Requests\Session;

/**
 * JPush response for a batch push notification.
 */
class JPushBatchResponse
{

    /**
     * Shared instance of a Logger class.
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Shared instance of the Requests\Session class.
     * @var Session
     */
    protected Session $http;

    /**
     * The statuses per endpoint.
     * @var array
     */
    private array $statuses;

    /**
     * Raw payload that was sent to JPush.
     * @var string
     */
    protected string $payload;

    /**
     * Message ID.
     * @var int
     */
    protected int $messageID;

    /**
     * Notification endpoints.
     * @var array
     */
    protected array $endpoints;

    /**
     * Constructor.
     *
     * @param Session         $http      Shared instance of the Requests\Session class.
     * @param LoggerInterface $logger    Shared instance of a Logger.
     * @param Response        $response  Requests\Response object.
     * @param string[]        $endpoints The endpoints the message was sent to (in the same order as sent).
     * @param string          $payload   Raw payload that was sent to JPush.
     */
    public function __construct(Session $http, LoggerInterface $logger, Response $response, array $endpoints, string $payload)
    {
        $this->statuses  = [];
        $this->http      = $http;
        $this->logger    = $logger;
        $this->payload   = $payload;
        $this->endpoints = $endpoints;

        if (!$response->success)
        {
            $this->report_error($this->endpoints, $response);
            return;
        }

        $jsonContent = json_decode($response->body, TRUE);

        if (!isset($jsonContent['msg_id']))
        {
            $this->report_error($this->endpoints, $response);
            return;
        }

        $this->messageID = (int) $jsonContent['msg_id'];
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->http);
        unset($this->logger);
        unset($this->statuses);
        unset($this->payload);
        unset($this->endpoints);
        unset($this->messageID);
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
        return $this->statuses[$endpoint] ?? PushNotificationStatus::Deferred;
    }

    /**
     * Get message_id of the batch.
     *
     * @return null|int Message id of the notification batch, NULL if batch failed
     */
    public function get_message_id(): ?int
    {
        return $this->messageID ?? NULL;
    }

    /**
     * Report an error with the push notification.
     *
     * @param string[] $endpoints The endpoints the message was sent to
     * @param Response $response  The HTTP Response
     *
     * @see https://docs.jiguang.cn/en/jpush/server/push/rest_api_v3_push/#call-return
     *
     * @return void
     */
    private function report_error(array &$endpoints, Response $response): void
    {
        $upstreamMessage = NULL;
        $upstreamCode    = NULL;

        if (!empty($response->body))
        {
            $body            = json_decode($response->body, TRUE);
            $upstreamMessage = $body['error']['message'] ?? NULL;
            $upstreamCode    = $body['error']['code'] ?? NULL;
        }

        $status = PushNotificationStatus::Error;

        // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
        switch ($response->status_code)
        {
            case 400:
                if ($upstreamCode === 1011)
                {
                    $status = PushNotificationStatus::InvalidEndpoint;
                }

                $errorMessage = $upstreamMessage ?? 'Invalid request';
                break;
            case 401:
                $errorMessage = $upstreamMessage ?? 'Error with authentication';
                break;
            case 403:
                $errorMessage = $upstreamMessage ?? 'Error with configuration';
                break;
            default:
                $errorMessage = $upstreamMessage ?? 'Unknown error';
                $status       = PushNotificationStatus::Unknown;
                break;
        }

        // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
        if ($response->status_code >= 500)
        {
            $errorMessage = $upstreamMessage ?? 'Internal error';
            $status       = PushNotificationStatus::TemporaryError;
        }

        foreach ($endpoints as $endpoint)
        {
            $this->statuses[$endpoint] = $status;
        }

        $context = [ 'error' => $errorMessage ];
        $this->logger->warning('Dispatching JPush notification failed: {error}', $context);
    }

}

?>
