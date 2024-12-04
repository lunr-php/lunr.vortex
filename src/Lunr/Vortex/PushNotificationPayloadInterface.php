<?php

/**
 * This file contains the PushNotificationPayloadInterface.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex;

/**
 * Push notification Payload interface.
 */
interface PushNotificationPayloadInterface
{

    /**
     * Check if the payload is for a broadcast notification.
     *
     * @return bool If payload for notification is a broadcast
     */
    public function is_broadcast(): bool;

}

?>
