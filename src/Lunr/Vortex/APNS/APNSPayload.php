<?php

/**
 * This file contains functionality to generate Apple Push Notification Service payloads.
 *
 * SPDX-FileCopyrightText: Copyright 2014 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS;

use ApnsPHP\Message\Priority;
use Lunr\Vortex\PushNotificationPayloadInterface;

/**
 * Apple Push Notification Service Payload Generator.
 */
class APNSPayload implements PushNotificationPayloadInterface
{

    /**
     * Array of Push Notification elements.
     * @var array
     */
    protected array $elements;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->elements = [];

        $this->elements['priority'] = Priority::Immediately;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->elements);
    }

    /**
     * Check if the payload is for a broadcast notification.
     *
     * @return bool If payload for notification is a broadcast
     */
    public function is_broadcast(): bool
    {
        return FALSE;
    }

    /**
     * Construct the payload for the push notification.
     *
     * @return array APNSPayload elements
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
     * @return APNSPayload Self Reference
     */
    public function set_collapse_key(string $key): self
    {
        $this->elements['collapse_key'] = $key;

        return $this;
    }

    /**
     * Sets the payload key topic.
     *
     * An string that is used to identify the notification topic. This is usually the app bundle identifier.
     *
     * @param string $topic The notification topic identifier
     *
     * @return APNSPayload Self Reference
     */
    public function set_topic(string $topic): self
    {
        $this->elements['topic'] = $topic;

        return $this;
    }

    /**
     * Mark the notification priority.
     *
     * @param Priority $priority Notification priority value.
     *
     * @return APNSPayload Self Reference
     */
    public function set_priority(Priority $priority): self
    {
        $this->elements['priority'] = $priority;

        return $this;
    }

    /**
     * Sets the payload badge index.
     *
     * Used to determine what type of icon to show on the app icon when the message arrives
     *
     * @param int $badge The badge index
     *
     * @return APNSPayload Self Reference
     */
    public function set_badge(int $badge): self
    {
        $this->elements['badge'] = $badge;

        return $this;
    }

    /**
     * Sets the payload sound.
     *
     * @param string $sound The sound to set it to
     *
     * @return APNSPayload Self Reference
     */
    public function set_sound(string $sound): self
    {
        $this->elements['sound'] = $sound;

        return $this;
    }

    /**
     * Sets the payload thread_id.
     *
     * @param string $thread_id The thread_id to set it to
     *
     * @return APNSPayload Self Reference
     */
    public function set_thread_id(string $thread_id): self
    {
        $this->elements['thread_id'] = $thread_id;

        return $this;
    }

    /**
     * Sets the payload identifier.
     *
     * @param string $identifier The identifier to set it to
     *
     * @return APNSPayload Self Reference
     */
    public function set_identifier(string $identifier): self
    {
        $this->elements['identifier'] = $identifier;

        return $this;
    }

    /**
     * Sets the payload category.
     *
     * @param string $category The category to set it to
     *
     * @return APNSPayload Self Reference
     */
    public function set_category(string $category): self
    {
        $this->elements['category'] = $category;

        return $this;
    }

    /**
     * Sets the payload content_available property.
     *
     * @param bool $content_available If there is content available for download
     *
     * @return APNSPayload Self Reference
     */
    public function set_content_available(bool $content_available): self
    {
        $this->elements['content_available'] = $content_available;

        return $this;
    }

    /**
     * Sets the payload mutable_content property.
     *
     * @param bool $mutable_content If the notification is mutable
     *
     * @return APNSPayload Self Reference
     */
    public function set_mutable_content(bool $mutable_content): self
    {
        $this->elements['mutable_content'] = $mutable_content;

        return $this;
    }

    /**
     * Sets the payload title.
     *
     * @param string $title The actual title
     *
     * @return APNSPayload Self Reference
     */
    public function set_title(string $title): self
    {
        $this->elements['title'] = $title;

        return $this;
    }

    /**
     * Sets the payload body.
     *
     * @param string $body The actual message
     *
     * @return APNSPayload Self Reference
     */
    public function set_body(string $body): self
    {
        $this->elements['body'] = $body;

        return $this;
    }

    /**
     * Sets the payload alert.
     *
     * The alert key represents the actual message to be sent
     * and it is named alert for sake of convention, as this is
     * the name of the key in the actual bytestream payload.
     *
     * @param string $alert The actual message
     *
     * @deprecated use set_body instead
     *
     * @return APNSPayload Self Reference
     */
    public function set_alert(string $alert): self
    {
        return $this->set_body($alert);
    }

    /**
     * Sets custom data in the payload.
     *
     * @param string $key   The key of the custom property
     * @param mixed  $value The value of the custom property
     *
     * @return APNSPayload Self Reference
     */
    public function set_custom_data(string $key, $value): self
    {
        if (!isset($this->elements['custom_data']))
        {
            $this->elements['custom_data'] = [];
        }

        $this->elements['custom_data'][$key] = $value;

        return $this;
    }

}

?>
