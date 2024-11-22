<?php

/**
 * This file contains functionality to generate Firebase Cloud Messaging Push Notification payloads for apns.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM;

/**
 * Firebase Cloud Messaging Push Notification APNS Payload Generator.
 *
 * @phpstan-type FcmApnsOptions array{
 *     analytics_label?: string,
 *     image?: string
 * }
 * @phpstan-type FcmApnsConfig array{
 *     headers?: array<string,string>,
 *     payload?: array<string, mixed>,
 *     fcm_options?: FcmApnsOptions
 * }
 */
class FCMApnsPayload
{

    /**
     * Array of Push Notification elements.
     * @var FcmApnsConfig
     */
    protected array $elements;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->elements = [];
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->elements);
    }

    /**
     * Construct the apns payload for the fcm push notification.
     *
     * @return FcmApnsConfig
     */
    public function get_payload(): array
    {
        return $this->elements;
    }

    /**
     * Sets the payload key collapse_key.
     *
     * An arbitrary string that is used to collapse a group of alike messages
     * when the device is offline, so that only the last message gets sent to the client.
     *
     * @param string $key The notification collapse key identifier
     *
     * @return $this
     */
    public function set_collapse_key(string $key): static
    {
        $this->elements['headers']['apns-collapse-id'] = $key;

        return $this;
    }

    /**
     * Sets the notification as providing content.
     *
     * @param bool $val Value for the "content_available" field.
     *
     * @return $this
     */
    public function set_content_available(bool $val): static
    {
        $this->elements['payload']['aps']['content-available'] = (int) $val;

        return $this;
    }

    /**
     * Mark the notification as mutable.
     *
     * @param bool $mutable Notification mutable_content value.
     *
     * @return $this
     */
    public function set_mutable_content(bool $mutable): static
    {
        $this->elements['payload']['aps']['mutable-content'] = (int) $mutable;

        return $this;
    }

    /**
     * Mark the notification priority.
     *
     * @param string $priority Notification priority value.
     *
     * @return $this
     */
    public function set_priority(string $priority): static
    {
        $value = FCMApnsPriority::tryFromString($priority);
        if ($value !== NULL)
        {
            $this->elements['headers']['apns-priority'] = (string) $value->value;
        }

        return $this;
    }

    /**
     * Sets the payload category.
     *
     * @param string $category The category to set it to
     *
     * @return $this
     */
    public function set_category(string $category): static
    {
        $this->elements['payload']['aps']['category'] = $category;

        return $this;
    }

    /**
     * Sets the notification sound.
     *
     * @param string $sound The sound to set it to
     *
     * @return $this
     */
    public function set_sound(string $sound): static
    {
        $this->elements['payload']['aps']['sound'] = $sound;

        return $this;
    }

}

?>
