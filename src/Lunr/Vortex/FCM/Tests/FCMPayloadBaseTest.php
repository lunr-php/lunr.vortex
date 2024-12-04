<?php

/**
 * This file contains the FCMPayloadBaseTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2017 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

/**
 * This class contains the Base tests of the FCMPayload class.
 *
 * @covers Lunr\Vortex\FCM\FCMPayload
 */
class FCMPayloadBaseTest extends FCMPayloadTest
{

    /**
     * Test elements is initialized with empty array.
     */
    public function testElementsIsInitializedWithEmptyArray(): void
    {
        $this->assertPropertySame('elements', []);
    }

    /**
     * Test android_payload is initialized with NULL.
     */
    public function testAndroidPayloadIsInitializedWithNULL(): void
    {
        $this->assertPropertySame('android_payload', NULL);
    }

    /**
     * Test apns_payload is initialized with NULL.
     */
    public function testApnsPayloadIsInitializedWithNULL(): void
    {
        $this->assertPropertySame('apns_payload', NULL);
    }

    /**
     * Test is_broadcast() returns FALSE when no condition or topic is set.
     *
     * @covers Lunr\Vortex\FCM\FCMPayload::is_broadcast
     */
    public function testIsBroadcastReturnFalse(): void
    {
        $this->assertFalse($this->class->is_broadcast());
    }

    /**
     * Test if is_broadcast() returns TRUE when condition is set.
     *
     * @covers Lunr\Vortex\FCM\FCMPayload::is_broadcast
     */
    public function testIsBroadcastConditionIsSet(): void
    {
        $this->class->set_condition("'TopicA' in topics && 'TopicB' in topics");

        $this->assertTrue($this->class->is_broadcast());
    }

    /**
     * Test if is_broadcast() returns TRUE when topic is set.
     *
     * @covers Lunr\Vortex\FCM\FCMPayload::is_broadcast
     */
    public function testIsBroadcastTopicIsSet(): void
    {
        $this->class->set_topic('news');

        $this->assertTrue($this->class->is_broadcast());
    }

}

?>
