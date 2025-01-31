<?php

/**
 * This file contains the EmailResponseGetErrorStatusTest class.
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
class EmailResponseGetErrorStatusTest extends EmailResponseTestCase
{

    /**
     * Testcase Constructor.
     */
    public function setUp(): void
    {
        parent::setUpError();
    }

    /**
     * Test that get_status() returns PushNotificationStatus::Error
     * for an endpoint with a failed notification.
     *
     * @covers Lunr\Vortex\Email\EmailResponse::get_status
     */
    public function testGetErrorStatusForEndpoint(): void
    {
        $this->assertEquals(PushNotificationStatus::Error, $this->class->get_status('error-endpoint'));
    }

}

?>
