<?php

/**
 * This file contains functionality to generate Firebase Cloud Messaging Push Notification payloads.
 *
 * SPDX-FileCopyrightText: Copyright 2017 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM;

use BadMethodCallException;
use InvalidArgumentException;

/**
 * Firebase Cloud Messaging Push Notification Payload Generator.
 *
 * @phpstan-import-type FcmAndroidConfig from FCMAndroidPayload
 * @phpstan-import-type FcmApnsConfig from FCMApnsPayload
 * @phpstan-type FcmNotification array{
 *     title?: string,
 *     body?: string,
 *     image?: string
 * }
 * @phpstan-type FcmOptions array{
 *     analytics_label?: string
 * }
 * @phpstan-type FcmPayloadData array{
 *     name?: string,
 *     data?: array<string,string>,
 *     notification?: FcmNotification,
 *     android?: FcmAndroidConfig,
 *     apns?: FcmApnsConfig,
 *     fcm_options?: FcmOptions,
 *     token?: string,
 *     topic?: string,
 *     condition?: string
 * }
 * @phpstan-type FcmOptionKeys "analytics_label"
 */
class FCMPayload
{

    /**
     * Array of Push Notification elements.
     * @var FcmPayloadData
     */
    protected array $elements;

    /**
     * The Android payload Push Notification Element.
     * @var ?FCMAndroidPayload
     */
    protected ?FCMAndroidPayload $android_payload;

    /**
     * The Apns payload Push Notification Element.
     * @var ?FCMApnsPayload
     */
    protected ?FCMApnsPayload $apns_payload;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->elements        = [];
        $this->android_payload = NULL;
        $this->apns_payload    = NULL;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->elements);
        unset($this->android_payload);
        unset($this->apns_payload);
    }

    /**
     * Construct the payload for the push notification.
     *
     * @param int $flag The flag to encode the payload with
     *
     * @return string FCMPayload
     */
    public function get_json_payload(int $flag = 0): string
    {
        $payload = $this->elements;

        if ($this->android_payload !== NULL)
        {
            $payload['android'] = $this->android_payload->get_payload();
        }

        if ($this->apns_payload !== NULL)
        {
            $payload['apns'] = $this->apns_payload->get_payload();
        }

        return json_encode([ 'message' => $payload ], $flag);
    }

    /**
     * Sets the payload key data.
     *
     * The fields of data represent the key-value pairs of the message's payload data.
     *
     * @param array<string,string> $data The actual notification information
     *
     * @return $this
     */
    public function set_data(array $data): static
    {
        foreach ($data as $key => &$value)
        {
            // @phpstan-ignore-next-line
            if (is_string($value) === FALSE)
            {
                throw new InvalidArgumentException('Data type of ' . $key . ' must be a string!');
            }
        }

        unset($value);

        $this->elements['data'] = $data;

        return $this;
    }

    /**
     * Check whether a condition is set
     *
     * @return bool TRUE if condition is present.
     */
    public function has_condition(): bool
    {
        return isset($this->elements['condition']);
    }

    /**
     * Check whether a condition is set
     *
     * @return bool TRUE if condition is present.
     */
    public function has_topic(): bool
    {
        return isset($this->elements['topic']);
    }

    /**
     * Sets the payload key notification.
     *
     * The fields of data represent the key-value pairs of the message's payload notification data.
     *
     * @param FcmNotification $notification The actual notification information
     *
     * @return $this
     */
    public function set_notification(array $notification): static
    {
        $this->elements['notification'] = $notification;

        return $this;
    }

    /**
     * Sets the topic name to send the message to.
     *
     * @param string $topic String of the topic name
     *
     * @return $this
     */
    public function set_topic(string $topic): static
    {
        if (isset($this->elements['token']))
        {
            throw new BadMethodCallException('Set FCM topic failed: Token is already set');
        }

        if (isset($this->elements['condition']))
        {
            throw new BadMethodCallException('Set FCM topic failed: Condition is already set');
        }

        $this->elements['topic'] = $topic;

        return $this;
    }

    /**
     * Sets the condition to send the message to. For example:
     * 'TopicA' in topics && ('TopicB' in topics || 'TopicC' in topics)
     *
     * You can include up to five topics in your conditional expression.
     * Conditions support the following operators: &&, ||, !
     *
     * @param string $condition Key-value pairs of payload data
     *
     * @return $this
     */
    public function set_condition(string $condition): static
    {
        if (isset($this->elements['token']))
        {
            throw new BadMethodCallException('Set FCM condition failed: Token is already set');
        }

        if (isset($this->elements['topic']))
        {
            throw new BadMethodCallException('Set FCM condition failed: Topic is already set');
        }

        $this->elements['condition'] = $condition;

        return $this;
    }

    /**
     * Set additional FCM values in the 'fcm_options' key.
     *
     * @param FcmOptionKeys $key   Options key.
     * @param string        $value Options value.
     *
     * @return $this
     */
    public function set_options(string $key, string $value): static
    {
        $this->elements['fcm_options'][$key] = $value;

        return $this;
    }

    /**
     * Set the token of the target for the notification.
     *
     * @param string $token Token of the target for the notification.
     *
     * @return $this
     */
    public function set_token(string $token): static
    {
        if (isset($this->elements['topic']))
        {
            throw new BadMethodCallException('Set FCM token failed: Topic is already set');
        }

        if (isset($this->elements['condition']))
        {
            throw new BadMethodCallException('Set FCM token failed: Condition is already set');
        }

        $this->elements['token'] = $token;

        return $this;
    }

    /**
     * Returns a reference of the android payload element.
     *
     * @return FCMAndroidPayload
     */
    public function android_payload(): FCMAndroidPayload
    {
        $this->android_payload ??= new FCMAndroidPayload();

        return $this->android_payload;
    }

    /**
     * Returns a reference of the apns payload element.
     *
     * @return FCMApnsPayload
     */
    public function apns_payload(): FCMApnsPayload
    {
        $this->apns_payload ??= new FCMApnsPayload();

        return $this->apns_payload;
    }

}

?>
