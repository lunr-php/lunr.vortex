<?php

/**
 * This file contains an abstraction for the response from the FCM server.
 *
 * SPDX-FileCopyrightText: Copyright 2017 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM;

use Lunr\Vortex\PushNotificationBroadcastResponseInterface;
use Lunr\Vortex\PushNotificationResponseInterface;
use Lunr\Vortex\PushNotificationStatus;

/**
 * Firebase Cloud Messaging Push Notification response wrapper.
 */
class FCMResponse implements PushNotificationResponseInterface, PushNotificationBroadcastResponseInterface
{

    /**
     * The statuses per endpoint.
     * @var array<string, PushNotificationStatus::*>
     */
    protected array $statuses;

    /**
     * The status for a broadcast.
     * @var PushNotificationStatus
     */
    protected PushNotificationStatus $broadcastStatus;

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
        unset($this->broadcastStatus);
    }

    /**
     * Add the results of a batch response.
     *
     * @param FCMBatchResponse $batchResponse Batch response
     * @param string[]         $endpoints     Endpoints of the batch
     *
     * @return void
     */
    public function add_batch_response(FCMBatchResponse $batchResponse, array $endpoints): void
    {
        foreach ($endpoints as $endpoint)
        {
            $this->statuses[$endpoint] = $batchResponse->get_status($endpoint);
        }
    }

    /**
     * Add the results of a batch response.
     *
     * @param FCMBatchResponse $batchResponse Push response.
     *
     * @return void
     */
    public function add_broadcast_response(FCMBatchResponse $batchResponse): void
    {
        $this->broadcastStatus = $batchResponse->get_broadcast_status();
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
        return isset($this->statuses[$endpoint]) ? $this->statuses[$endpoint] : PushNotificationStatus::Unknown;
    }

    /**
     * Get notification delivery status for a broadcast.
     *
     * @return PushNotificationStatus Delivery status for the broadcast
     */
    public function get_broadcast_status(): PushNotificationStatus
    {
        return $this->broadcastStatus ?? PushNotificationStatus::Unknown;
    }

}

?>
