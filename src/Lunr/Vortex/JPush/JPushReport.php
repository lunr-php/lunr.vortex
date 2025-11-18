<?php

/**
 * This file contains the JPushReport class.
 *
 * SPDX-FileCopyrightText: Copyright 2023 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush;

use Lunr\Vortex\PushNotificationStatus;
use Psr\Log\LoggerInterface;
use WpOrg\Requests\Exception as RequestsException;
use WpOrg\Requests\Exception\Http as RequestsExceptionHTTP;
use WpOrg\Requests\Response;
use WpOrg\Requests\Session;

/**
 * JPush report for push notifications.
 */
class JPushReport
{

    /**
     * JPush Report API URL.
     * @var string
     */
    private const JPUSH_REPORT_URL = 'https://report.jpush.cn/v3/status/message';

    /**
     * Shared instance of a Logger class.
     *
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Shared instance of the Requests\Session class.
     * @var Session
     */
    protected Session $http;

    /**
     * Push Notification authentication token.
     * @var string|null
     */
    protected ?string $authToken;

    /**
     * The statuses per endpoint.
     * @var array<string,PushNotificationStatus>
     */
    private array $statuses;

    /**
     * Constructor.
     *
     * @param Session         $http   Shared instance of the Requests\Session class.
     * @param LoggerInterface $logger Shared instance of a Logger.
     */
    public function __construct(Session $http, LoggerInterface $logger)
    {
        $this->statuses  = [];
        $this->http      = $http;
        $this->logger    = $logger;
        $this->authToken = NULL;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->http);
        unset($this->logger);
        unset($this->statuses);
        unset($this->authToken);
    }

    /**
     * Fetch report from JPush and set statuses when report is fetched successfully
     *
     * @param int      $messageID JPush Batch ID
     * @param string[] $endpoints The endpoints the message was sent to (in the same order as sent).
     *
     * @return void
     */
    public function get_report(int $messageID, array $endpoints): void
    {
        $payload = [
            'msg_id'           => $messageID,
            'registration_ids' => $endpoints,
        ];

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Basic ' . $this->authToken,
        ];

        try
        {
            $response = $this->http->post(self::JPUSH_REPORT_URL, $headers, json_encode($payload), []);
            $response->throw_for_status();
        }
        catch (RequestsExceptionHTTP $e)
        {
            /** @var Response $response */
            $response = $e->getData();

            $this->report_error($response, $endpoints);
            return;
        }
        catch (RequestsException $e)
        {
            foreach ($endpoints as $endpoint)
            {
                $this->statuses[$endpoint] = PushNotificationStatus::Error;
            }

            $context = [ 'error' => $e->getMessage() ];
            $this->logger->warning('Getting JPush notification report failed: {error}', $context);
            return;
        }

        foreach (json_decode($response->body, TRUE) as $endpoint => $result)
        {
            if ($result['status'] === 0)
            {
                $this->statuses[$endpoint] = PushNotificationStatus::Success;
            }
            else
            {
                $this->report_endpoint_error($endpoint, $result['status']);
            }
        }
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
        return $this->statuses[$endpoint] ?? PushNotificationStatus::Unknown;
    }

    /**
     * Report an error with the push notification.
     *
     * @param Response $response  The HTTP Response
     * @param string[] $endpoints The endpoints the message was sent to (in the same order as sent).
     *
     * @see https://docs.jiguang.cn/en/jpush/server/push/rest_api_v3_push/#call-return
     *
     * @return void
     */
    private function report_error(Response $response, array &$endpoints): void
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
                if ($upstreamCode === 3002)
                {
                    $status = PushNotificationStatus::Deferred;
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
        $this->logger->warning('Getting JPush notification report failed: {error}', $context);
    }

    /**
     * Report an error with the push notification for one endpoint.
     *
     * @param string $endpoint  Endpoint for which the push failed
     * @param string $errorCode Error response code
     *
     * @see https://docs.jiguang.cn/en/jpush/server/push/rest_api_v3_report/#inquiry-of-service-status
     *
     * @return void
     */
    private function report_endpoint_error(string $endpoint, string $errorCode): void
    {
        switch ($errorCode)
        {
            case 1:
                $status       = PushNotificationStatus::Deferred;
                $errorMessage = 'Not delivered';
                break;
            case 2:
                $status       = PushNotificationStatus::InvalidEndpoint;
                $errorMessage = 'Registration_id does not belong to the application';
                break;
            case 3:
                $status       = PushNotificationStatus::Error;
                $errorMessage = 'Registration_id belongs to the application, but it is not the target of the message';
                break;
            case 4:
                $status       = PushNotificationStatus::TemporaryError;
                $errorMessage = 'The system is abnormal';
                break;
            default:
                $status       = PushNotificationStatus::Unknown;
                $errorMessage = $errorCode;
                break;
        }

        $context = [ 'endpoint' => $endpoint, 'error' => $errorMessage ];
        $this->logger->warning('Dispatching JPush notification failed for endpoint {endpoint}: {error}', $context);

        $this->statuses[$endpoint] = $status;
    }

    /**
     * Set the the auth token for the http headers.
     *
     * @param string $authToken The auth token for the JPush push notifications
     *
     * @return void
     */
    public function set_auth_token(string $authToken): void
    {
        $this->authToken = $authToken;
    }

}

?>
