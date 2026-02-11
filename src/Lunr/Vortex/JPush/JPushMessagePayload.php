<?php

/**
 * This file contains functionality to generate JPush Message payloads.
 *
 * SPDX-FileCopyrightText: Copyright 2020 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush;

/**
 * JPush Message Payload Generator.
 *
 * @phpstan-type JPushMessagePayloadElements array{
 *    platform: string[],
 *    audience: array<string, mixed>,
 *    message?: array<string, mixed>,
 *    options?: array<string, string|int|float|bool>
 * }
 */
class JPushMessagePayload extends JPushPayload
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
     * @return JPushMessagePayloadElements JPushPayload
     */
    public function get_payload(): array
    {
        $elements = $this->elements;

        unset($elements['notification']);
        unset($elements['notification_3rd']);

        return $elements;
    }

}

?>
