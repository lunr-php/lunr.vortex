<?php

/**
 * This file contains functionality to dispatch Firebase Cloud Messaging Push Notifications.
 *
 * SPDX-FileCopyrightText: Copyright 2017 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM;

use InvalidArgumentException;
use Lunr\Vortex\PushNotificationMultiDispatcherInterface;
use Psr\Log\LoggerInterface;
use WpOrg\Requests\Exception as RequestsException;
use WpOrg\Requests\Response;
use WpOrg\Requests\Session;

/**
 * Firebase Cloud Messaging Push Notification Dispatcher.
 */
class FCMDispatcher extends FCMBaseApi implements PushNotificationMultiDispatcherInterface
{
    /**
     * Url to send the FCM push notification to.
     * @var string
     */
    private const GOOGLE_SEND_URL = 'https://fcm.googleapis.com/v1/projects/';

    /**
     * Constructor.
     *
     * @param Session         $http   Shared instance of the Requests\Session class.
     * @param LoggerInterface $logger Shared instance of a Logger.
     */
    public function __construct(Session $http, LoggerInterface $logger)
    {
        parent::__construct($http, $logger);
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Getter for FCMResponse.
     *
     * @return FCMResponse
     */
    public function get_response(): FCMResponse
    {
        return new FCMResponse();
    }

    /**
     * Getter for FCMBatchResponse.
     *
     * @param array<string|int,Response|RequestsException> $http_responses Array of Requests\Response object.
     * @param LoggerInterface                              $logger         Shared instance of a Logger.
     * @param string[]                                     $endpoints      The endpoints the message was sent to.
     *
     * @return FCMBatchResponse
     */
    public function get_batch_response(array $http_responses, LoggerInterface $logger, array $endpoints): FCMBatchResponse
    {
        return new FCMBatchResponse($http_responses, $logger, $endpoints);
    }

    /**
     * Push the notification.
     *
     * @param object $payload   Payload object
     * @param array  $endpoints Endpoints to send to in this batch
     *
     * @return FCMResponse Response object
     */
    public function push(object $payload, array &$endpoints): FCMResponse
    {
        if (!$payload instanceof FCMPayload)
        {
            throw new InvalidArgumentException('Invalid payload object!');
        }

        if ($endpoints === [] && !$payload->has_topic() && !$payload->has_condition())
        {
            throw new InvalidArgumentException('No target provided!');
        }

        $fcm_response = $this->get_response();

        if ($this->oauth_token === NULL || $this->project_id === NULL)
        {
            if ($this->oauth_token === NULL)
            {
                $http_code = 401;
                $error_msg = 'Tried to push FCM notification but wasn\'t authenticated.';
            }
            else
            {
                $http_code = 400;
                $error_msg = 'Tried to push FCM notification but project id is not provided.';
            }

            $this->logger->warning($error_msg);

            $http_response = $this->get_new_response_object_for_failed_request($http_code);

            $fcm_response->add_batch_response($this->get_batch_response([ $http_response ], $this->logger, $endpoints), $endpoints);

            return $fcm_response;
        }

        if ($payload->has_topic() || $payload->has_condition())
        {
            $fcm_response->add_batch_response($this->push_batch($payload, $endpoints), $endpoints);

            return $fcm_response;
        }

        foreach (array_chunk($endpoints, self::BATCH_SIZE) as &$batch)
        {
            $batch_response = $this->push_batch($payload, $batch);

            $fcm_response->add_batch_response($batch_response, $batch);

            unset($batch_response);
        }

        unset($batch);

        return $fcm_response;
    }

    /**
     * Push the notification to a batch of endpoints.
     *
     * @param object   $payload   Payload object
     * @param string[] $endpoints Endpoints to send to in this batch
     *
     * @return FCMBatchResponse Response object
     */
    public function push_batch(object $payload, array &$endpoints): FCMBatchResponse
    {
        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->oauth_token,
        ];

        $options = [
            'timeout'          => 30, // timeout in seconds
            'connect_timeout'  => 30, // timeout in seconds
            'protocol_version' => 2.0,
        ];

        $url = self::GOOGLE_SEND_URL . $this->project_id . '/messages:send';

        $responses = [];

        if ($payload->has_topic() || $payload->has_condition())
        {
            try
            {
                $responses[] = $this->http->post(
                    $url,
                    $headers,
                    $payload->get_json_payload(JSON_UNESCAPED_UNICODE),
                    $options
                );
            }
            catch (RequestsException $e)
            {
                $responses[] = $e;
            }
        }
        else
        {
            foreach ($endpoints as $endpoint)
            {
                try
                {
                    $responses[$endpoint] = $this->http->post(
                        $url,
                        $headers,
                        $payload->set_token($endpoint)->get_json_payload(JSON_UNESCAPED_UNICODE),
                        $options
                    );
                }
                catch (RequestsException $e)
                {
                    $responses[$endpoint] = $e;
                }
            }
        }

        return $this->get_batch_response($responses, $this->logger, $endpoints);
    }

    /**
     * Get a Requests\Response object for a failed request.
     *
     * @param int $http_code Set http code for the request.
     *
     * @return Response New instance of a Requests\Response object.
     */
    protected function get_new_response_object_for_failed_request(?int $http_code = NULL): Response
    {
        $http_response = new Response();

        $http_response->url = self::GOOGLE_SEND_URL . $this->project_id . '/messages:send';

        $http_response->status_code = $http_code ?? FALSE;

        return $http_response;
    }

}

?>
