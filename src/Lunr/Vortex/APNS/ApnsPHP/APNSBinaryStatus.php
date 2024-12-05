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
enum APNSBinaryStatus: int
{

    /**
     * No error encountered.
     */
    case Success = 0;

    /**
     * Processing error
     */
    case ProcessingError = 1;

    /**
     * Missing device token error
     */
    case MissingDeviceTokenError = 2;

    /**
     * Missing topic error
     */
    case TopicError = 3;

    /**
     * Missing payload error
     */
    case MissingPayloadError = 4;

    /**
     * Invalid token size error
     */
    case InvalidTokenSizeError = 5;

    /**
     * Invalid topic size error
     */
    case InvalidTopicSizeError = 6;

    /**
     * Invalid payload size error
     */
    case InvalidPayloadSizeError = 7;

    /**
     * Invalid token error
     */
    case InvalidTokenError = 8;

    /**
     * Shutdown error
     */
    case ShutdownError = 10;

    /**
     * Protocol error
     */
    case ProtocolError = 128;

    /**
     * Unknown error
     */
    case UnknownError = 255;

}

?>
