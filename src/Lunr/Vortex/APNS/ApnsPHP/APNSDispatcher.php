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
use Lunr\Vortex\APNS\APNSAlertPayload;
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
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->apns_push);
        unset($this->logger);
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
        if (!$payload instanceof APNSAlertPayload)
        {
            throw new InvalidArgumentException('Invalid payload object!');
        }

        // Create message
        $message = $this->build_message_for_payload($payload);

        // Add endpoints
        $invalid_endpoints = [];

        foreach ($endpoints as $endpoint)
        {
            try
            {
                $message->addRecipient($endpoint);
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
            $this->apns_push->add($message);
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
        return new APNSResponse($this->logger, $endpoints, $invalid_endpoints, $errors, (string) $message);
    }

    /**
     * Build a Message object for a given payload
     *
     * @param APNSAlertPayload $payload The payload to build from
     *
     * @return Message The filled message
     */
    private function build_message_for_payload(APNSAlertPayload $payload): Message
    {
        $payload = $payload->get_payload();
        $message = $this->get_new_apns_message();

        $message->setPriority($payload['priority']);

        if (isset($payload['title']))
        {
            $message->setTitle($payload['title']);
        }

        if (isset($payload['body']))
        {
            $message->setText($payload['body']);
        }

        if (isset($payload['thread_id']))
        {
            $message->setThreadID($payload['thread_id']);
        }

        if (isset($payload['topic']))
        {
            $message->setTopic($payload['topic']);
        }

        if (isset($payload['collapse_key']))
        {
            $message->setCollapseId($payload['collapse_key']);
        }

        if (isset($payload['identifier']))
        {
            $message->setCustomIdentifier($payload['identifier']);
        }

        if (isset($payload['sound']))
        {
            $message->setSound($payload['sound']);
        }

        if (isset($payload['category']))
        {
            $message->setCategory($payload['category']);
        }

        if (isset($payload['badge']))
        {
            $message->setBadge($payload['badge']);
        }

        if (isset($payload['content_available']))
        {
            $message->setContentAvailable($payload['content_available']);
        }

        if (isset($payload['mutable_content']))
        {
            $message->setMutableContent($payload['mutable_content']);
        }

        if (isset($payload['custom_data']))
        {
            foreach ($payload['custom_data'] as $key => $value)
            {
                $message->setCustomProperty($key, $value);
            }
        }

        return $message;
    }

}

?>
