<?php

/**
 * This file contains the APNSLiveActivityPayloadSetTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS\Tests;

use ApnsPHP\Message\LiveActivityEvent;

/**
 * This class contains tests for the setters of the APNSLiveActivityPayload class.
 *
 * @covers Lunr\Vortex\APNS\APNSLiveActivityPayload
 */
class APNSLiveActivityPayloadSetTest extends APNSLiveActivityPayloadTest
{

    /**
     * Test set_event() works correctly.
     *
     * @covers Lunr\Vortex\APNS\APNSLiveActivityPayload::set_event
     */
    public function testSetEvent(): void
    {
        $this->class->set_event(LiveActivityEvent::Start);

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('event', $value);
        $this->assertEquals(LiveActivityEvent::Start, $value['event']);
    }

    /**
     * Test fluid interface of set_event().
     *
     * @covers Lunr\Vortex\APNS\APNSLiveActivityPayload::set_event
     */
    public function testSetEventReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_event(LiveActivityEvent::Start));
    }

    /**
     * Test set_content_state() works correctly.
     *
     * @covers Lunr\Vortex\APNS\APNSLiveActivityPayload::set_content_state
     */
    public function testSetContentState(): void
    {
        $this->class->set_content_state([]);

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('contentState', $value);
        $this->assertEquals([], $value['contentState']);
    }

    /**
     * Test fluid interface of set_content_state().
     *
     * @covers Lunr\Vortex\APNS\APNSLiveActivityPayload::set_content_state
     */
    public function testSetContentStateReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_content_state([]));
    }

    /**
     * Test set_attributes() works correctly.
     *
     * @covers Lunr\Vortex\APNS\APNSLiveActivityPayload::set_attributes
     */
    public function testSetAttributes(): void
    {
        $this->class->set_attributes([]);

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('attributes', $value);
        $this->assertEquals([], $value['attributes']);
    }

    /**
     * Test fluid interface of set_attributes().
     *
     * @covers Lunr\Vortex\APNS\APNSLiveActivityPayload::set_attributes
     */
    public function testSetAttributesReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_attributes([]));
    }

    /**
     * Test set_attributes_type() works correctly.
     *
     * @covers Lunr\Vortex\APNS\APNSLiveActivityPayload::set_attributes_type
     */
    public function testSetAttributesType(): void
    {
        $this->class->set_attributes_type('type');

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('attributesType', $value);
        $this->assertEquals('type', $value['attributesType']);
    }

    /**
     * Test fluid interface of set_attributes_type().
     *
     * @covers Lunr\Vortex\APNS\APNSLiveActivityPayload::set_attributes_type
     */
    public function testSetAttributesTypeReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_attributes_type('type'));
    }

    /**
     * Test set_stale_timestamp() works correctly.
     *
     * @covers Lunr\Vortex\APNS\APNSLiveActivityPayload::set_stale_timestamp
     */
    public function testSetStaleTime(): void
    {
        $this->class->set_stale_timestamp(1);

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('staleTime', $value);
        $this->assertEquals(1, $value['staleTime']);
    }

    /**
     * Test fluid interface of set_stale_timestamp().
     *
     * @covers Lunr\Vortex\APNS\APNSLiveActivityPayload::set_stale_timestamp
     */
    public function testSetStaleTimeReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_stale_timestamp(1));
    }

    /**
     * Test set_dismiss_timestamp() works correctly.
     *
     * @covers Lunr\Vortex\APNS\APNSLiveActivityPayload::set_dismiss_timestamp
     */
    public function testSetDismissTime(): void
    {
        $this->class->set_dismiss_timestamp(1);

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('dismissTime', $value);
        $this->assertEquals(1, $value['dismissTime']);
    }

    /**
     * Test fluid interface of set_dismiss_timestamp().
     *
     * @covers Lunr\Vortex\APNS\APNSLiveActivityPayload::set_dismiss_timestamp
     */
    public function testSetDismissTimeReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_dismiss_timestamp(1));
    }

}

?>
