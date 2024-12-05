<?php

/**
 * This file contains functionality to generate Apple Push Notification Service payloads.
 *
 * SPDX-FileCopyrightText: Copyright 2014 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS;

/**
 * Apple Push Notification Service Payload Generator.
 * @phpstan-import-type APNSBasePayloadElements from APNSPayload
 */
class APNSAlertPayload extends APNSPayload
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
     * @return APNSBasePayloadElements APNSPayload elements
     */
    public function get_payload(): array
    {
        return parent::get_payload();
    }

}

?>
