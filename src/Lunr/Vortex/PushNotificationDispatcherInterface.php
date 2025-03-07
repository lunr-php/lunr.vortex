<?php

/**
 * This file contains the PushNotificationDispatcherInterface interface which
 * is the base of all push notifications.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex;

/**
 * Push notification interface.
 *
 * @phpstan-import-type Endpoint from PushNotificationDispatcher
 */
interface PushNotificationDispatcherInterface
{

    /**
     * Push the notification.
     *
     * @param object     $payload   Payload object
     * @param Endpoint[] $endpoints Endpoints to sent it to in this batch
     *
     * @return PushNotificationResponseInterface Response object
     */
    public function push(object $payload, array &$endpoints): PushNotificationResponseInterface;

}

?>
