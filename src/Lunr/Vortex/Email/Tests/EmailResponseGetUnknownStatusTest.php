<?php

/**
 * This file contains the EmailResponseGetUnknownStatusTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2014 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\Email\Tests;

use Lunr\Vortex\PushNotificationStatus;

/**
 * This class contains tests for the EmailResponse class.
 *
 * @covers Lunr\Vortex\Email\EmailResponse
 */
class EmailResponseGetUnknownStatusTest extends EmailResponseTestCase
{

    /**
     * Test that get_status() returns PushNotificationStatus::Unknown
     * when an unknown endpoint is passed in.
     *
     * @covers Lunr\Vortex\Email\EmailResponse::get_status
     */
    public function testGetUnknownStatusForEndpoint(): void
    {
        $this->assertEquals(PushNotificationStatus::Unknown, $this->class->get_status('unknown'));
    }

}

?>
