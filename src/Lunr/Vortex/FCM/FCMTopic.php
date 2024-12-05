<?php

/**
 * This file contains functionality for topics to Firebase Cloud Messaging Push Notifications.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM;

use Psr\Log\LoggerInterface;
use WpOrg\Requests\Exception as RequestsException;
use WpOrg\Requests\Session;

/**
 * Firebase Cloud Messaging Push Notification Topic helper.
 */
class FCMTopic extends FCMBaseApi
{
    /**
     * Url to (un)subscribe endpoints to a topic.
     * @var string
     */
    private const TOPIC_URL = 'https://iid.googleapis.com/iid/v1';

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
     * Subscribe to topic
     *
     * @param string   $topic     The name of the topic to subscribe to.
     * @param string[] $endpoints The endpoints to subscribe to the topic.
     *
     * @return void
     */
    public function subscribe(string $topic, array $endpoints): void
    {
        $this->manage_subscriptions($topic, $endpoints, TRUE);
    }

    /**
     * Unsubscribe to topic
     *
     * @param string   $topic     The name of the topic to unsubscribe to.
     * @param string[] $endpoints The endpoints to unsubscribe to the topic.
     *
     * @return void
     */
    public function unsubscribe(string $topic, array $endpoints): void
    {
        $this->manage_subscriptions($topic, $endpoints, FALSE);
    }

    /**
     * Subscribe or unsubscribe to topic
     *
     * @param string   $topic           The name of the topic to mange.
     * @param string[] $endpoints       The endpoints of the topic to manage.
     * @param bool     $is_subscription Whether its an subscription or unsubscription
     *
     * @see https://developers.google.com/instance-id/reference/server
     *
     * @return void
     */
    private function manage_subscriptions(string $topic, array $endpoints, bool $is_subscription): void
    {
        $url = self::TOPIC_URL . ($is_subscription ? ':batchAdd' : ':batchRemove');

        $subscription_task = $is_subscription ? 'Subscribing' : 'Unsubscribing';

        $headers = [
            'Content-Type'      => 'application/json',
            'Authorization'     => 'Bearer ' . $this->oauth_token,
            'access_token_auth' => 'true',
        ];

        $options = [
            'timeout'          => 30, // timeout in seconds
            'connect_timeout'  => 30, // timeout in seconds
            'protocol_version' => 2.0,
        ];

        foreach (array_chunk($endpoints, self::BATCH_SIZE) as $endpoints_chunk)
        {
            $body = [
                'to'                  => '/topics/' . $topic,
                'registration_tokens' => $endpoints_chunk,
            ];

            try
            {
                $http_response = $this->http->post($url, $headers, json_encode($body, JSON_UNESCAPED_SLASHES), $options);

                $http_response->throw_for_status();
            }
            catch (RequestsException $e)
            {
                $this->logger->warning(
                    $subscription_task . ' FCM endpoints to topic {topic} failed: {message}',
                    [ 'topic' => $topic, 'message' => $e->getMessage() ]
                );

                throw $e;
            }

            $response_body = json_decode($http_response->body, TRUE);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($response_body) || !isset($response_body['results']))
            {
                $this->logger->warning(
                    'Invalid response from FCM when ' . strtolower($subscription_task) . ' FCM endpoints to topic {topic}: {message}',
                    [ 'topic' => $topic, 'message' => $http_response->body ]
                );

                continue;
            }

            foreach ($response_body['results'] as $place => $endpoint_result)
            {
                if (empty($endpoint_result))
                {
                    continue;
                }

                if (array_key_exists('error', $endpoint_result))
                {
                    $this->logger->warning(
                        $subscription_task . ' FCM endpoint {endpoint} to topic {topic} failed: {message}',
                        [ 'endpoint' => $endpoints_chunk[$place],'topic' => $topic, 'message' => $endpoint_result['error'] ]
                    );
                }
            }
        }
    }

}

?>
