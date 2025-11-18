<?php

/**
 * This file contains an abstraction for the response from the JPush server.
 *
 * SPDX-FileCopyrightText: Copyright 2020 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush;

use Lunr\Vortex\PushNotificationDeferredResponseInterface;
use Lunr\Vortex\PushNotificationStatus;

/**
 * Google Cloud Messaging Push Notification response wrapper.
 */
class JPushResponse implements PushNotificationDeferredResponseInterface
{

    /**
     * The statuses per endpoint.
     * @var array<string,array{"status": PushNotificationStatus, "message_id": string|null}>
     */
    protected array $statuses;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->statuses = [];
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->statuses);
    }

    /**
     * Add the results of a batch response.
     *
     * @param JPushBatchResponse $batchResponse Batch response
     * @param string[]           $endpoints     Endpoints of the batch
     *
     * @return void
     */
    public function add_batch_response(JPushBatchResponse $batchResponse, array $endpoints): void
    {
        $messageID = $batchResponse->get_message_id();

        if ($messageID !== NULL)
        {
            $messageID = (string) $messageID;
        }

        foreach ($endpoints as $endpoint)
        {
            $this->statuses[$endpoint] = [
                'status'     => $batchResponse->get_status($endpoint),
                'message_id' => $messageID
            ];
        }
    }

    /**
     * Get notification delivery status for an endpoint.
     *
     * @param string $endpoint Endpoint
     *
     * @return PushNotificationStatus Delivery status for the endpoint
     */
    public function get_status(string $endpoint): PushNotificationStatus
    {
        return $this->statuses[$endpoint]['status'] ?? PushNotificationStatus::Unknown;
    }

    /**
     * Get message_id for an endpoint.
     *
     * @param string $endpoint Endpoint
     *
     * @return ?string Delivery batch info for an endpoint
     */
    public function get_message_id(string $endpoint): ?string
    {
        return $this->statuses[$endpoint]['message_id'] ?? NULL;
    }

}

?>
