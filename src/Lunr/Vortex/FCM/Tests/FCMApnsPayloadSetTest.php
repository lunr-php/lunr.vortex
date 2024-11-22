<?php

/**
 * This file contains the FCMApnsPayloadSetTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2018 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

/**
 * This class contains tests for the setters of the FCMApnsPayload class.
 *
 * @covers \Lunr\Vortex\FCM\FCMApnsPayload
 */
class FCMApnsPayloadSetTest extends FCMApnsPayloadTest
{

    /**
     * Test set_collapse_key() works correctly.
     *
     * @covers Lunr\Vortex\FCM\FCMApnsPayload::set_collapse_key
     */
    public function testSetCollapseKey(): void
    {
        $this->class->set_collapse_key('test');

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('headers', $value);
        $this->assertArrayHasKey('apns-collapse-id', $value['headers']);
        $this->assertEquals('test', $value['headers']['apns-collapse-id']);
    }

    /**
     * Test fluid interface of set_collapse_key().
     *
     * @covers Lunr\Vortex\FCM\FCMApnsPayload::set_collapse_key
     */
    public function testSetCollapseKeyReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_collapse_key('collapse_key'));
    }

    /**
     * Test set_content_available() works correctly.
     *
     * @covers Lunr\Vortex\FCM\FCMApnsPayload::set_content_available
     */
    public function testSetContentAvailable(): void
    {
        $this->class->set_content_available(TRUE);

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('payload', $value);
        $this->assertArrayHasKey('aps', $value['payload']);
        $this->assertArrayHasKey('content-available', $value['payload']['aps']);
        $this->assertEquals(1, $value['payload']['aps']['content-available']);
    }

    /**
     * Test fluid interface of set_content_available().
     *
     * @covers Lunr\Vortex\FCM\FCMApnsPayload::set_content_available
     */
    public function testSetContentAvailableReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_content_available(TRUE));
    }

    /**
     * Test set_mutable_content() works correctly.
     *
     * @covers Lunr\Vortex\FCM\FCMApnsPayload::set_mutable_content
     */
    public function testSetMutableContent(): void
    {
        $this->class->set_mutable_content(TRUE);

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('payload', $value);
        $this->assertArrayHasKey('aps', $value['payload']);
        $this->assertArrayHasKey('mutable-content', $value['payload']['aps']);
        $this->assertEquals(1, $value['payload']['aps']['mutable-content']);
    }

    /**
     * Test fluid interface of set_mutable_content().
     *
     * @covers Lunr\Vortex\FCM\FCMApnsPayload::set_mutable_content
     */
    public function testSetMutableContentReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_mutable_content(TRUE));
    }

    /**
     * Provide different priority options for testing.
     *
     * @return array
     */
    public function priorityProvider(): array
    {
        $return = [];

        $return['lowercase'] = [ 'high', 10 ];
        $return['titlecase'] = [ 'High', 10 ];
        $return['uppercase'] = [ 'HIGH', 10 ];
        $return['default']   = [ 'default', 5 ];
        $return['low']       = [ 'low', 1 ];

        return $return;
    }

    /**
     * Test set_priority() works correctly.
     *
     * @dataProvider priorityProvider
     *
     * @param string $priority The priority to set
     * @param int    $expected The expected value
     *
     * @covers \Lunr\Vortex\FCM\FCMApnsPayload::set_priority
     */
    public function testSetPriority(string $priority, int $expected): void
    {
        $this->class->set_priority($priority);

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('headers', $value);
        $this->assertArrayHasKey('apns-priority', $value['headers']);
        $this->assertEquals($expected, $value['headers']['apns-priority']);
    }

    /**
     * Test fluid interface of set_priority().
     *
     * @covers \Lunr\Vortex\FCM\FCMApnsPayload::set_priority
     */
    public function testSetPriorityReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_priority('high'));
    }

    /**
     * Test set_category() works correctly.
     *
     * @covers \Lunr\Vortex\FCM\FCMApnsPayload::set_category
     */
    public function testSetCategory()
    {
        $this->class->set_category('category');

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('payload', $value);
        $this->assertArrayHasKey('aps', $value['payload']);
        $this->assertArrayHasKey('category', $value['payload']['aps']);
        $this->assertEquals('category', $value['payload']['aps']['category']);
    }

    /**
     * Test fluid interface of set_category().
     *
     * @covers \Lunr\Vortex\FCM\FCMApnsPayload::set_category
     */
    public function testSetCategoryReturnsSelfReference()
    {
        $this->assertSame($this->class, $this->class->set_category('category'));
    }

    /**
     * Test set_sound() works correctly.
     *
     * @covers \Lunr\Vortex\FCM\FCMApnsPayload::set_sound
     */
    public function testSetSound()
    {
        $this->class->set_sound('sound');

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('payload', $value);
        $this->assertArrayHasKey('aps', $value['payload']);
        $this->assertArrayHasKey('sound', $value['payload']['aps']);
        $this->assertEquals('sound', $value['payload']['aps']['sound']);
    }

    /**
     * Test fluid interface of set_sound().
     *
     * @covers \Lunr\Vortex\FCM\FCMApnsPayload::set_sound
     */
    public function testSetSoundReturnsSelfReference()
    {
        $this->assertSame($this->class, $this->class->set_sound('sound'));
    }

}

?>
