<?php

/**
 * This file contains the JPushPayloadBaseTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2020 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush\Tests;

/**
 * This class contains the Base tests of the JPushPayload class.
 *
 * @covers \Lunr\Vortex\JPush\JPushNotificationPayload
 */
class JPushPayloadBaseTest extends JPushPayloadTest
{

    /**
     * Test elements is initialized.
     *
     * @covers \Lunr\Vortex\JPush\JPushPayload::__construct
     */
    public function testElementsIsInitialized(): void
    {
        $this->assertPropertySame('elements', [
            'platform' => [ 'ios', 'android' ],
            'audience' => [],
            'notification' => [],
            'notification_3rd' => [],
            'message' => []
        ]);
    }

    /**
     * Test is_broadcast returns false.
     *
     * @covers \Lunr\Vortex\JPush\JPushPayload::is_broadcast
     */
    public function testIsBroadCastReturnFalse(): void
    {
        $this->assertFalse($this->class->is_broadcast());
    }

}

?>
