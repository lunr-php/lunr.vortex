<?php

/**
 * This file contains the APNSPayloadBaseTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS\Tests;

/**
 * This class contains tests for the  APNSPayload class.
 *
 * @covers Lunr\Vortex\APNS\APNSPayload
 */
class APNSPayloadBaseTest extends APNSPayloadTest
{

    /**
     * Test is_broadcast returns false.
     *
     * @covers Lunr\Vortex\APNS\APNSPayload::is_broadcast
     */
    public function testIsBroadCastReturnFalse(): void
    {
        $this->assertFalse($this->class->is_broadcast());
    }

}

?>
