<?php

/**
 * This file contains priority types for delivering APNS Notifications.
 *
 * SPDX-FileCopyrightText: Copyright 2021 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS;

/**
 * APNS Priority Types.
 */
enum APNSPriority: int
{

    /**
     * Deliver notification immediately.
     */
    case High = 10;

    /**
     * Deliver notification with normal priority.
     */
    case Normal = 5;

}

?>
