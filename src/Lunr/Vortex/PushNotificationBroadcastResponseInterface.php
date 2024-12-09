<?php

/**
 * This file contains the PushNotificationBroadcastResponseInterface.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex;

/**
 * Push notification Response interface.
 */
interface PushNotificationBroadcastResponseInterface
{

    /**
     * Get notification delivery status for the broadcast.
     *
     * @return PushNotificationStatus Delivery status for the broadcast
     */
    public function get_broadcast_status(): PushNotificationStatus;

}

?>
