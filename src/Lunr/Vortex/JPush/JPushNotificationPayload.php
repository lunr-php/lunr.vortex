<?php

/**
 * This file contains functionality to generate JPush Notification payloads.
 *
 * SPDX-FileCopyrightText: Copyright 2020 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush;

use ReflectionClass;

/**
 * JPush Notification Payload Generator.
 *
 * @phpstan-type JPushNotificationPayloadElements array{
 *    platform: string[],
 *    audience: array<string, mixed>,
 *    notification?: array<string, mixed>,
 *    options?: array<string, string|int|float|bool>
 * }
 */
class JPushNotificationPayload extends JPushPayload
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->set_priority(JPushPriority::HIGH);
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
     * @return JPushNotificationPayloadElements JPushPayload
     */
    public function get_payload(): array
    {
        $elements = $this->elements;

        unset($elements['message']);
        unset($elements['notification_3rd']);

        return $elements;
    }

    /**
     * Sets the payload sound data.
     *
     * @param string $sound The notification sound
     *
     * @return $this Self Reference
     */
    public function set_sound(string $sound): static
    {
        return $this->set_notification_data('sound', $sound);
    }

    /**
     * Sets the notification as providing content.
     *
     * @param bool $val Value for the "content_available" field.
     *
     * @return $this Self Reference
     */
    public function set_content_available(bool $val): static
    {
        return $this->set_notification_data('content-available', $val, [ 'ios' ]);
    }

    /**
     * Mark the notification as mutable.
     *
     * @param bool $mutable Notification mutable_content value.
     *
     * @return $this Self Reference
     */
    public function set_mutable_content(bool $mutable): static
    {
        return $this->set_notification_data('mutable-content', $mutable, [ 'ios' ]);
    }

    /**
     * Mark the notification priority.
     *
     * @param int $priority Notification priority value.
     *
     * @return $this Self Reference
     */
    public function set_priority(int $priority): static
    {
        $priority_class = new ReflectionClass('\Lunr\Vortex\JPush\JPushPriority');
        $priorities     = array_values($priority_class->getConstants());
        if (in_array($priority, $priorities, TRUE))
        {
            $this->set_notification_data('priority', $priority, [ 'android' ]);
        }

        return $this;
    }

}

?>
