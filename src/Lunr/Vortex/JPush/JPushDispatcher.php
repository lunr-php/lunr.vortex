<?php

/**
 * This file contains functionality to dispatch JPush Push Notifications.
 *
 * SPDX-FileCopyrightText: Copyright 2020 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush;

use InvalidArgumentException;
use Lunr\Vortex\PushNotificationMultiDispatcherInterface;
use Psr\Log\LoggerInterface;
use WpOrg\Requests\Exception as RequestsException;
use WpOrg\Requests\Response;
use WpOrg\Requests\Session;

/**
 * JPush Push Notification Dispatcher.
 */
class JPushDispatcher implements PushNotificationMultiDispatcherInterface
{

    /**
     * Maximum number of endpoints allowed in one push.
     * @var integer
     */
    private const BATCH_SIZE = 1000;

    /**
     * Url to send the JPush push notification to.
     * @var string
     */
    private const JPUSH_SEND_URL = 'https://api.jpush.cn/v3/push';

    /**
     * Push Notification authentication token.
     * @var string|null
     */
    protected ?string $authToken;

    /**
     * Shared instance of the Requests\Session class.
     * @var Session
     */
    protected Session $http;

    /**
     * Shared instance of a Logger class.
     *
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param Session         $http   Shared instance of the Requests\Session class.
     * @param LoggerInterface $logger Shared instance of a Logger.
     */
    public function __construct(Session $http, LoggerInterface $logger)
    {
        $this->http      = $http;
        $this->logger    = $logger;
        $this->authToken = NULL;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->authToken);
        unset($this->http);
        unset($this->logger);
    }

    /**
     * Getter for JPushResponse.
     *
     * @return JPushResponse
     */
    public function get_response(): JPushResponse
    {
        return new JPushResponse();
    }

    /**
     * Getter for JPushBatchResponse.
     *
     * @param Response $httpResponse Requests\Response object.
     * @param string[] $endpoints    The endpoints the message was sent to (in the same order as sent).
     * @param string   $payload      Raw payload that was sent to JPush.
     *
     * @return JPushBatchResponse
     */
    public function get_batch_response(Response $httpResponse, array $endpoints, string $payload): JPushBatchResponse
    {
        return new JPushBatchResponse($this->http, $this->logger, $httpResponse, $endpoints, $payload);
    }

    /**
     * Push the notification.
     *
     * @param object   $payload   Payload object
     * @param string[] $endpoints Endpoints to send to in this batch
     *
     * @return JPushResponse Response object
     */
    public function push(object $payload, array &$endpoints): JPushResponse
    {
        if (!$payload instanceof JPushPayload)
        {
            throw new InvalidArgumentException('Invalid payload object!');
        }

        $response = $this->get_response();

        foreach (array_chunk($endpoints, self::BATCH_SIZE) as &$batch)
        {
            $batchResponse = $this->push_batch($payload, $batch);

            $response->add_batch_response($batchResponse, $batch);

            unset($batchResponse);
        }

        unset($batch);

        return $response;
    }

    /**
     * Push the notification to a batch of endpoints.
     *
     * @param JPushPayload $payload   Payload object
     * @param string[]     $endpoints Endpoints to send to in this batch
     *
     * @return JPushBatchResponse Response object
     */
    protected function push_batch(JPushPayload $payload, array &$endpoints): JPushBatchResponse
    {
        $tmpPayload                                = $payload->get_payload();
        $tmpPayload['audience']['registration_id'] = $endpoints;

        $jsonPayload = json_encode($tmpPayload, JSON_UNESCAPED_UNICODE);
        $options     = [
            'timeout'         => 15, // timeout in seconds
            'connect_timeout' => 15  // timeout in seconds
        ];

        try
        {
            $httpResponse = $this->http->post(self::JPUSH_SEND_URL, [], $jsonPayload, $options);
        }
        catch (RequestsException $e)
        {
            $this->logger->warning(
                'Dispatching JPush notification(s) failed: {message}',
                [ 'message' => $e->getMessage() ]
            );
            $httpResponse = $this->get_new_response_object_for_failed_request();

            if ($e->getType() == 'curlerror' && curl_errno($e->getData()) == 28)
            {
                // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
                $httpResponse->status_code = 500;
            }
        }

        return $this->get_batch_response($httpResponse, $endpoints, $jsonPayload);
    }

    /**
     * Set the the auth token for the http headers.
     *
     * @param string $authToken The auth token for the JPush push notifications
     *
     * @return JPushDispatcher Self reference
     */
    public function set_auth_token(string $authToken): self
    {
        $this->authToken = $authToken;

        $this->http->headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Basic ' . $this->authToken,
        ];

        return $this;
    }

    /**
     * Get a Requests\Response object for a failed request.
     *
     * @return Response New instance of a Requests\Response object.
     */
    protected function get_new_response_object_for_failed_request(): Response
    {
        $httpResponse = new Response();

        $httpResponse->url = self::JPUSH_SEND_URL;

        return $httpResponse;
    }

}

?>
