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
 * Apple Push Notification Service status reasons.
 */
enum APNSHttpStatusReason: string
{

    /**
     * Bad token error.
     */
    case BadTokenError = 'BadDeviceToken';

    /**
     * Bad collapse ID error.
     */
    case BadCollapseIdError = 'BadCollapseId';

    /**
     * Bad expiration date error.
     */
    case BadExpirationDateError = 'BadExpirationDate';

    /**
     * Bad message ID error.
     */
    case BadMessageIdError = 'BadMessageId';

    /**
     * Bad priority error.
     */
    case BadPriorityError = 'BadPriority';

    /**
     * Bad topic error.
     */
    case BadTopicError = 'BadTopic';

    /**
     * Token not for current topic error.
     */
    case NonMatchingTokenError = 'DeviceTokenNotForTopic';

    /**
     * Idle timeout.
     */
    case IdleTimeoutError = 'IdleTimeout';

    /**
     * Topic not allowed error.
     */
    case TopicBlockedError = 'TopicDisallowed';

    /**
     * Certificate is not valid.
     */
    case CertificateInvalidError = 'BadCertificate';

    /**
     * Certificate does not match requested environment.
     */
    case CertificateEnvironmentError = 'BadCertificateEnvironment';

    /**
     * JWT Provider token is expired.
     */
    case AuthTokenExpiredError = 'ExpiredProviderToken';

    /**
     * JWT Provider token is invalid.
     */
    case InvalidAuthTokenError = 'InvalidProviderToken';

    /**
     * JWT Provider token is missing.
     */
    case MissingAuthTokenError = 'MissingProviderToken';

    /**
     * Action is forbidden.
     */
    case ForbiddenError = 'Forbidden';

}

?>
