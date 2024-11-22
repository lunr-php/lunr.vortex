<?php

/**
 * This file contains priority types for Firebase Cloud Messaging Notifications to APNS.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM;

/**
 * Firebase Cloud Messaging APNS Priority Types.
 */
enum FCMApnsPriority: int
{

    /**
     * Prioritize the device’s power considerations over all other factors for delivery,
     * and prevent awakening the device
     */
    case Low = 1;

    /**
     * Send the notification based on power considerations on the user’s device
     */
    case Default = 5;

    /**
     * Send the notification immediately
     */
    case High = 10;

    /**
     * Get a case based on the string value.
     *
     * @param string $value The string representation of the case
     *
     * @return self|null
     */
    public static function tryFromString(string $value): ?self
    {
        return match(strtolower($value))
        {
            'low'     => self::Low,
            'default' => self::Default,
            'high'    => self::High,
            default   => NULL,
        };
    }

}

?>
