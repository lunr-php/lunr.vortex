<?php

/**
 * This file contains an abstraction for the response from the APNS server.
 *
 * SPDX-FileCopyrightText: Copyright 2016 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS\ApnsPHP;

use Lunr\Vortex\PushNotificationResponseInterface;
use Lunr\Vortex\PushNotificationStatus;
use Psr\Log\LoggerInterface;

/**
 * Apple Push Notification Service response wrapper.
 */
class APNSResponse implements PushNotificationResponseInterface
{

    /**
     * Shared instance of a Logger class.
     *
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * The statuses per endpoint.
     * @var array<PushNotificationStatus>
     */
    protected array $statuses;

    /**
     * Raw payload that was sent to APNS.
     * @var string
     */
    protected string $payload;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger           Shared instance of a Logger.
     * @param array           $endpoints        The endpoints the message was sent to
     * @param array           $invalidEndpoints List of invalid endpoints detected before the push.
     * @param array|null      $errors           The errors response from the APNS Push.
     * @param string          $payload          Raw payload that was sent to APNS.
     */
    public function __construct(LoggerInterface $logger, array $endpoints, array $invalidEndpoints, ?array $errors, string $payload)
    {
        $this->logger   = $logger;
        $this->statuses = [];
        $this->payload  = $payload;

        $this->report_invalid_endpoints($invalidEndpoints);

        if (!is_null($errors))
        {
            $this->set_statuses($endpoints, $errors);
        }
        else
        {
            $this->report_error($endpoints);
        }
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->logger);
        unset($this->statuses);
        unset($this->payload);
    }

    /**
     * Define the status result for each endpoint.
     *
     * @param array $endpoints The endpoints the message was sent to
     * @param array $errors    The errors response from the APNS Push.
     *
     * @return void
     */
    protected function set_statuses(array $endpoints, array $errors): void
    {
        foreach ($errors as $error)
        {
            $message = $error['MESSAGE'];

            foreach ($error['ERRORS'] as $subError)
            {
                $statusCode    = $subError['statusCode'];
                $statusMessage = $subError['statusMessage'];
                $reason        = NULL;
                $messageData   = json_decode($statusMessage, TRUE);
                if (json_last_error() === JSON_ERROR_NONE)
                {
                    $reason = $messageData['reason'] ?? NULL;
                }

                switch (APNSHttpStatus::tryFrom($statusCode))
                {
                    case APNSHttpStatus::BadRequestError:
                    case APNSHttpStatus::UnregisteredError:
                        $status = PushNotificationStatus::InvalidEndpoint;
                        break;
                    case APNSHttpStatus::TooManyRequestsError:
                        $status = PushNotificationStatus::TemporaryError;
                        break;
                    default:
                        $status = PushNotificationStatus::Unknown;
                        break;
                }

                if ($reason !== NULL)
                {
                    //Refine based on reasons in the HTTP status
                    switch (APNSHttpStatusReason::tryFrom($reason))
                    {
                        case APNSHttpStatusReason::TopicBlockedError:
                        case APNSHttpStatusReason::CertificateInvalidError:
                        case APNSHttpStatusReason::CertificateEnvironmentError:
                        case APNSHttpStatusReason::InvalidAuthTokenError:
                            $status = PushNotificationStatus::Error;
                            break;
                        case APNSHttpStatusReason::IdleTimeoutError:
                        case APNSHttpStatusReason::AuthTokenExpiredError:
                            $status = PushNotificationStatus::TemporaryError;
                            break;
                        case APNSHttpStatusReason::BadTokenError:
                        case APNSHttpStatusReason::NonMatchingTokenError:
                            $status = PushNotificationStatus::InvalidEndpoint;
                            break;
                        default:
                            break;
                    }
                }

                $endpoint = $message->getRecipient();

                $this->statuses[$endpoint] = $status;

                $context = [ 'endpoint' => $endpoint, 'error' => $reason ?? $statusMessage ];
                $this->logger->warning('Dispatching APNS notification failed for endpoint {endpoint}: {error}', $context);
            }
        }

        foreach ($endpoints as $endpoint)
        {
            if (isset($this->statuses[$endpoint]))
            {
                continue;
            }

            $this->statuses[$endpoint] = PushNotificationStatus::Success;
        }
    }

    /**
     * Report invalid endpoints.
     *
     * @param array $invalidEndpoints The invalid endpoints
     *
     * @return void
     */
    protected function report_invalid_endpoints(array &$invalidEndpoints): void
    {
        foreach ($invalidEndpoints as $invalidEndpoint)
        {
            $this->statuses[$invalidEndpoint] = PushNotificationStatus::InvalidEndpoint;
        }
    }

    /**
     * Report an error with the push notification.
     *
     * @param array $endpoints The endpoints the message was sent to
     *
     * @return void
     */
    protected function report_error(array &$endpoints): void
    {
        foreach ($endpoints as $endpoint)
        {
            if (isset($this->statuses[$endpoint]))
            {
                continue;
            }

            $this->statuses[$endpoint] = PushNotificationStatus::Error;
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

}

?>
