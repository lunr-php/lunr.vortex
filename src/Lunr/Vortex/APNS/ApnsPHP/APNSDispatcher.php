<?php

/**
 * This file contains functionality to dispatch Apple Push Notification Service.
 *
 * SPDX-FileCopyrightText: Copyright 2016 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS\ApnsPHP;

use ApnsPHP\Exception as ApnsPHPException;
use ApnsPHP\Message;
use ApnsPHP\Message\Exception as MessageException;
use ApnsPHP\Push;
use InvalidArgumentException;
use Lunr\Vortex\APNS\APNSPayload;
use Lunr\Vortex\PushNotificationMultiDispatcherInterface;
use Psr\Log\LoggerInterface;

/**
 * Apple Push Notification Service Push Notification Dispatcher.
 */
class APNSDispatcher implements PushNotificationMultiDispatcherInterface
{

    /**
     * Shared instance of ApnsPHP\Push.
     *
     * @var Push
     */
    protected Push $apns_push;

    /**
     * Apns Message instance
     *
     * @var Message
     */
    protected Message $apns_message;

    /**
     * Shared instance of a Logger class.
     *
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger    Shared instance of a Logger.
     * @param Push            $apns_push Apns Push instance.
     */
    public function __construct(LoggerInterface $logger, Push $apns_push)
    {
        $this->logger    = $logger;
        $this->apns_push = $apns_push;

        $this->reset();
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->apns_push);
        unset($this->apns_message);
        unset($this->logger);
    }

    /**
     * Reset the variable members of the class.
     *
     * @return void
     */
    protected function reset(): void
    {
        unset($this->apns_message);
    }

    /**
     * Return a new APNS message.
     *
     * @return Message
     */
    protected function get_new_apns_message(): Message
    {
        return new Message();
    }

    /**
     * Push the notification.
     *
     * @param object $payload   Payload object
     * @param array  $endpoints Endpoints to send to in this batch
     *
     * @return APNSResponse Response object
     */
    public function push(object $payload, array &$endpoints): APNSResponse
    {
        if (!$payload instanceof APNSPayload)
        {
            throw new InvalidArgumentException('Invalid payload object!');
        }

        // Create message
        $payload = $payload->get_payload();

        $this->apns_message = $this->get_new_apns_message();

        try
        {
            if (isset($payload['title']))
            {
                $this->apns_message->setTitle($payload['title']);
            }

            if (isset($payload['body']))
            {
                $this->apns_message->setText($payload['body']);
            }

            if (isset($payload['thread_id']))
            {
                $this->apns_message->setThreadID($payload['thread_id']);
            }

            if (isset($payload['topic']))
            {
                $this->apns_message->setTopic($payload['topic']);
            }

            if (isset($payload['priority']))
            {
                $this->apns_message->setPriority($payload['priority']->value);
            }

            if (isset($payload['collapse_key']))
            {
                $this->apns_message->setCollapseId($payload['collapse_key']);
            }

            if (isset($payload['identifier']))
            {
                $this->apns_message->setCustomIdentifier($payload['identifier']);
            }

            if (isset($payload['sound']))
            {
                $this->apns_message->setSound($payload['sound']);
            }

            if (isset($payload['category']))
            {
                $this->apns_message->setCategory($payload['category']);
            }

            if (isset($payload['badge']))
            {
                $this->apns_message->setBadge($payload['badge']);
            }

            if (isset($payload['content_available']))
            {
                $this->apns_message->setContentAvailable($payload['content_available']);
            }

            if (isset($payload['mutable_content']))
            {
                $this->apns_message->setMutableContent($payload['mutable_content']);
            }

            if (isset($payload['custom_data']))
            {
                foreach ($payload['custom_data'] as $key => $value)
                {
                    $this->apns_message->setCustomProperty($key, $value);
                }
            }
        }
        catch (MessageException $e)
        {
            $this->logger->warning($e->getMessage());
        }

        // Add endpoints
        $invalid_endpoints = [];

        foreach ($endpoints as $endpoint)
        {
            try
            {
                $this->apns_message->addRecipient($endpoint);
            }
            catch (MessageException $e)
            {
                $invalid_endpoints[] = $endpoint;

                $this->logger->warning($e->getMessage());
            }
        }

        // Send message
        try
        {
            $this->apns_push->add($this->apns_message);
            $this->apns_push->connect();
            $this->apns_push->send();
            $this->apns_push->disconnect();

            $errors = $this->apns_push->getErrors();
        }
        catch (ApnsPHPException $e)
        {
            $errors = NULL;

            $context = [ 'error' => $e->getMessage() ];
            $this->logger->warning('Dispatching APNS notification failed: {error}', $context);
        }

        // Return response
        $response = new APNSResponse($this->logger, $endpoints, $invalid_endpoints, $errors, (string) $this->apns_message);

        $this->reset();

        return $response;
    }

}

?>
