<?php

/**
 * This file contains the JPushPayloadSetTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2020 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush\Tests;

/**
 * This class contains the Set tests of the JPushPayload class.
 *
 * @covers \Lunr\Vortex\JPush\JPushPayload
 */
class JPushPayloadSetTest extends JPushPayloadTestCase
{

    /**
     * Test set_collapse_key() works correctly.
     *
     * @covers \Lunr\Vortex\JPush\JPushPayload::set_collapse_key
     */
    public function testSetCollapseKey(): void
    {
        $this->class->set_collapse_key('test');

        $value = $this->getReflectionPropertyValue('elements');

        $this->assertArrayHasKey('apns_collapse_id', $value['options']);
        $this->assertEquals('test', $value['options']['apns_collapse_id']);
    }

    /**
     * Test fluid interface of set_collapse_key().
     *
     * @covers \Lunr\Vortex\JPush\JPushPayload::set_collapse_key
     */
    public function testSetCollapseKeyReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_collapse_key('collapse_key'));
    }

    /**
     * Test set_time_to_live() works correctly.
     *
     * @covers \Lunr\Vortex\JPush\JPushPayload::set_time_to_live
     */
    public function testSetTimeToLive(): void
    {
        $this->class->set_time_to_live(5);

        $value = $this->getReflectionPropertyValue('elements');

        $this->assertArrayHasKey('time_to_live', $value['options']);
        $this->assertEquals(5, $value['options']['time_to_live']);
    }

    /**
     * Test fluid interface of set_time_to_live().
     *
     * @covers \Lunr\Vortex\JPush\JPushPayload::set_time_to_live
     */
    public function testSetTimeToLiveReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_time_to_live(1));
    }

    /**
     * Test set_data() works correctly.
     *
     * @covers \Lunr\Vortex\JPush\JPushPayload::set_data
     */
    public function testSetData(): void
    {
        $this->class->set_data([ 'key' => 'value' ]);

        $value = $this->getReflectionPropertyValue('elements');

        $this->assertArrayHasKey('extras', $value['notification']['android']);
        $this->assertEquals([ 'key' => 'value' ], $value['notification']['android']['extras']);
        $this->assertEquals([ 'key' => 'value' ], $value['notification_3rd']['extras']);
        $this->assertEquals([ 'key' => 'value' ], $value['message']['extras']);
    }

    /**
     * Test fluid interface of set_data().
     *
     * @covers \Lunr\Vortex\JPush\JPushPayload::set_data
     */
    public function testSetDataReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_data([]));
    }

    /**
     * Test set_notification_identifier() works correctly.
     *
     * @covers \Lunr\Vortex\JPush\JPushPayload::set_notification_identifier
     */
    public function testSetNotificationIdentifier(): void
    {
        $this->class->set_notification_identifier('ID');

        $value = $this->getReflectionPropertyValue('elements');

        $this->assertArrayHasKey('cid', $value);
        $this->assertEquals('ID', $value['cid']);
    }

    /**
     * Test fluid interface of set_notification_identifier().
     *
     * @covers \Lunr\Vortex\JPush\JPushPayload::set_notification_identifier
     */
    public function testSetNotificationIdentifierReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_notification_identifier('ID'));
    }

    /**
     * Test set_body() works correctly.
     *
     * @covers \Lunr\Vortex\JPush\JPushPayload::set_body
     */
    public function testSetBody(): void
    {
        $this->class->set_body('BODY');

        $value = $this->getReflectionPropertyValue('elements');

        $this->assertArrayHasKey('alert', $value['notification']['android']);
        $this->assertEquals('BODY', $value['notification']['android']['alert']);
        $this->assertEquals('BODY', $value['notification_3rd']['content']);
        $this->assertEquals('BODY', $value['message']['msg_content']);
    }

    /**
     * Test fluid interface of set_body().
     *
     * @covers \Lunr\Vortex\JPush\JPushPayload::set_body
     */
    public function testSetBodyReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_body('body'));
    }

    /**
     * Test set_title() works correctly.
     *
     * @covers \Lunr\Vortex\JPush\JPushPayload::set_title
     */
    public function testSetTitle(): void
    {
        $this->class->set_title('Title');

        $value = $this->getReflectionPropertyValue('elements');

        $this->assertArrayHasKey('title', $value['notification']['android']);
        $this->assertEquals('Title', $value['notification']['android']['title']);
        $this->assertEquals('Title', $value['notification_3rd']['title']);
        $this->assertEquals('Title', $value['message']['title']);
    }

    /**
     * Test fluid interface of set_title().
     *
     * @covers \Lunr\Vortex\JPush\JPushPayload::set_title
     */
    public function testSetTitleReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_title('title'));
    }

    /**
     * Test set_category() works correctly.
     *
     * @covers \Lunr\Vortex\JPush\JPushPayload::set_category
     */
    public function testSetCategory(): void
    {
        $this->class->set_category('cats');

        $value = $this->getReflectionPropertyValue('elements');

        $this->assertArrayHasKey('category', $value['notification']['android']);
        $this->assertEquals('cats', $value['notification']['android']['category']);
        $this->assertEquals('cats', $value['message']['content_type']);
    }

    /**
     * Test fluid interface of set_category().
     *
     * @covers \Lunr\Vortex\JPush\JPushPayload::set_category
     */
    public function testSetCategoryReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_category('cats'));
    }

    /**
     * Test set_content_available() works correctly.
     *
     * @covers \Lunr\Vortex\JPush\JPushPayload::set_options
     */
    public function testSetOptions()
    {
        $this->class->set_options('analytics_label', 'fooBar');

        $value = $this->getReflectionPropertyValue('elements');

        $this->assertArrayHasKey('options', $value);
        $this->assertArrayHasKey('analytics_label', $value['options']);
        $this->assertEquals('fooBar', $value['options']['analytics_label']);
    }

    /**
     * Test fluid interface of set_content_available().
     *
     * @covers \Lunr\Vortex\JPush\JPushPayload::set_options
     */
    public function testSetOptionsReturnsSelfReference()
    {
        $this->assertSame($this->class, $this->class->set_options('analytics_label', 'fooBar'));
    }

}

?>
