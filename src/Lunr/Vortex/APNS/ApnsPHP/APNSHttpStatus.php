<?php

/**
 * This file contains Apple Push Notification Service stream status codes.
 *
 * SPDX-FileCopyrightText: Copyright 2016 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS\ApnsPHP;

/**
 * Apple Push Notification Service status codes.
 */
enum APNSHttpStatus: int
{

    /**
     * No error encountered
     */
    case Success = 200;

    /**
     * Bad request error
     */
    case BadRequestError = 400;

    /**
     * Certificate or token error
     */
    case AuthenticationError = 403;

    /**
     * The device token is inactive for the specified topic
     */
    case UnregisteredError = 410;

    /**
     * The message payload was too large
     */
    case PayloadTooLargeError = 413;

    /**
     * The provider token is being updated too often
     */
    case TooManyRequestsError = 429;

    /**
     * Unknown internal error
     */
    case InternalError = 500;

    /**
     * Shutdown error
     */
    case ShutdownError = 503;

}

?>
