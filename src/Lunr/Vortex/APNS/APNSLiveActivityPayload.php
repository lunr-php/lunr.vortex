<?php

/**
 * This file contains functionality to generate Apple Push Notification Service payloads for live activities.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS;

use ApnsPHP\Message\LiveActivityEvent;

/**
 * Apple Push Notification Service live activity payload generator.
 *
 * @phpstan-import-type APNSBasePayloadElements from APNSPayload
 * @phpstan-type APNSLiveActivityPayloadElements APNSBasePayloadElements|array{
 *     event?: LiveActivityEvent,
 *     contentState?: array|object,
 *     attributesType?: string,
 *     attributes?: array|object,
 *     staleTime?: int,
 *     dismissTime?: int,
 * }
 */
class APNSLiveActivityPayload extends APNSPayload
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Construct the payload for the push notification.
     *
     * @return APNSLiveActivityPayloadElements APNSPayload elements
     */
    public function get_payload(): array
    {
        return parent::get_payload();
    }

    /**
     * Sets the payload key event.
     *
     * @param LiveActivityEvent $event The event type for the live activity
     *
     * @return self Self Reference
     */
    public function set_event(LiveActivityEvent $event): self
    {
        $this->elements['event'] = $event;

        return $this;
    }

    /**
     * Sets the payload key contentState.
     *
     * @param array|object $state The current content state for the live activity
     *
     * @return self Self Reference
     */
    public function set_content_state(array|object $state): self
    {
        $this->elements['contentState'] = $state;

        return $this;
    }

    /**
     * Sets the payload key attributes.
     *
     * @param array|object $attributes The starting attributes for the live activity
     *
     * @return self Self Reference
     */
    public function set_attributes(array|object $attributes): self
    {
        $this->elements['attributes'] = $attributes;

        return $this;
    }

    /**
     * Sets the payload key attributesType.
     *
     * @param string $type The starting attributes type for the live activity
     *
     * @return self Self Reference
     */
    public function set_attributes_type(string $type): self
    {
        $this->elements['attributesType'] = $type;

        return $this;
    }

    /**
     * Sets the payload key staleTime.
     *
     * @param int $time The timestamp when the activity should become stale
     *
     * @return self Self Reference
     */
    public function set_stale_timestamp(int $time): self
    {
        $this->elements['staleTime'] = $time;

        return $this;
    }

    /**
     * Sets the payload key dismissTime.
     *
     * @param int $time The timestamp when the activity should dismiss
     *
     * @return self Self Reference
     */
    public function set_dismiss_timestamp(int $time): self
    {
        $this->elements['dismissTime'] = $time;

        return $this;
    }

}

?>
