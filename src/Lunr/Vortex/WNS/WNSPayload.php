<?php

/**
 * This file contains functionality to generate Windows Push Notification payloads.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\WNS;

use Lunr\Vortex\PushNotificationPayloadInterface;

/**
 * Windows Push Notification Payload Generator.
 */
abstract class WNSPayload implements PushNotificationPayloadInterface
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        // no-op
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        // no-op
    }

    /**
     * Escape a string for use in the payload.
     *
     * @param string $string String to escape
     *
     * @return string Escaped string
     */
    protected function escape_string(string $string): string
    {
        $search  = [ '&', '<', '>', '‘', '“' ];
        $replace = [ '&amp;', '&lt;', '&gt;', '&apos;', '&quot;' ];

        return str_replace($search, $replace, $string);
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
     * @return string Payload
     */
    abstract public function get_payload(): string;

}

?>
