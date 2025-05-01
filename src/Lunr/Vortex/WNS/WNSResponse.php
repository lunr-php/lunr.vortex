<?php

/**
 * This file contains an abstraction for the response from the WNS server.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\WNS;

use Lunr\Vortex\PushNotificationResponseInterface;
use Lunr\Vortex\PushNotificationStatus;
use Psr\Log\LoggerInterface;
use WpOrg\Requests\Response;
use WpOrg\Requests\Response\Headers;

/**
 * Windows Push Notification response wrapper.
 */
class WNSResponse implements PushNotificationResponseInterface
{

    /**
     * HTTP headers of the response.
     * @var Headers<string,string>
     */
    private Headers $headers;

    /**
     * HTTP status code.
     * @var int|bool
     */
    private readonly int|bool $http_code;

    /**
     * Delivery status.
     * @var PushNotificationStatus
     */
    private readonly PushNotificationStatus $status;

    /**
     * Push notification endpoint.
     * @var string
     */
    private readonly string $endpoint;

    /**
     * Raw payload that was sent to WNS.
     * @var string|null
     */
    protected readonly ?string $payload;

    /**
     * Constructor.
     *
     * @param Response        $response Requests\Response object.
     * @param LoggerInterface $logger   Shared instance of a Logger.
     * @param string|null     $payload  Raw payload that was sent to WNS.
     */
    public function __construct(Response $response, LoggerInterface $logger, ?string $payload)
    {
        $this->http_code = $response->status_code;
        $this->endpoint  = $response->url;
        $this->payload   = $payload;

        if ($this->http_code === FALSE)
        {
            $this->status = PushNotificationStatus::Error;
        }
        else
        {
            $this->headers = $response->headers;
            $this->status  = $this->parseStatus($response->url, $logger);
        }
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->headers);
    }

    /**
     * Set notification status information.
     *
     * @param string          $endpoint The notification endpoint that was used.
     * @param LoggerInterface $logger   Shared instance of a Logger.
     *
     * @return PushNotificationStatus The detected status of the notification
     */
    private function parseStatus(string $endpoint, LoggerInterface $logger): PushNotificationStatus
    {
        switch ($this->http_code)
        {
            case 200:
                if ($this->headers['X-WNS-Status'] === 'received')
                {
                    $status = PushNotificationStatus::Success;
                }
                elseif ($this->headers['X-WNS-Status'] === 'channelthrottled')
                {
                    $status = PushNotificationStatus::TemporaryError;
                }
                else
                {
                    $status = PushNotificationStatus::ClientError;
                }

                break;
            case 404:
            case 410:
                $status = PushNotificationStatus::InvalidEndpoint;
                break;
            case 400:
            case 401:
            case 403:
            case 405:
            case 413:
                $status = PushNotificationStatus::Error;
                break;
            case 406:
            case 500:
            case 503:
                $status = PushNotificationStatus::TemporaryError;
                break;
            default:
                $status = PushNotificationStatus::Unknown;
                break;
        }

        if ($status !== PushNotificationStatus::Success)
        {
            $context = [
                'endpoint'          => $endpoint,
                'nstatus'           => $this->headers['X-WNS-Status'],
                'dstatus'           => $this->headers['X-WNS-DeviceConnectionStatus'],
                'error_description' => $this->headers['X-WNS-Error-Description'],
                'error_trace'       => $this->headers['X-WNS-Debug-Trace'],
            ];

            $message  = 'Push notification delivery status for endpoint {endpoint}: ';
            $message .= '{nstatus}, device {dstatus}, description {error_description}, trace {error_trace}';

            $logger->warning($message, $context);
        }

        return $status;
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
        if ($endpoint != $this->endpoint)
        {
            return PushNotificationStatus::Unknown;
        }

        return $this->status;
    }

}

?>
