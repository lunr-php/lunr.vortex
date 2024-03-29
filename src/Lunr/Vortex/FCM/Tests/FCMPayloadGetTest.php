<?php

/**
 * This file contains the FCMPayloadGetTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use Lunr\Vortex\FCM\FCMAndroidPayload;

/**
 * This class contains tests for the getters of the FCMPayload class.
 *
 * @covers Lunr\Vortex\FCM\FCMPayload
 */
class FCMPayloadGetTest extends FCMPayloadTest
{

    /**
     * Test get_json_payload() with collapse_key being present.
     *
     * @covers Lunr\Vortex\FCM\FCMPayload::get_json_payload
     */
    public function testGetJsonPayloadWithCollapseKey(): void
    {
        $file     = TEST_STATICS . '/Vortex/fcm/collapse_key.json';
        $elements = [ 'collapse_key' => 'test' ];

        $this->set_reflection_property_value('elements', $elements);

        $this->assertStringMatchesFormatFile($file, $this->class->get_json_payload());
    }

    /**
     * Test get_json_payload() with data being present.
     *
     * @covers Lunr\Vortex\FCM\FCMPayload::get_json_payload
     */
    public function testGetJsonPayloadWithData(): void
    {
        $file     = TEST_STATICS . '/Vortex/fcm/data.json';
        $elements = [
            'data' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
        ];

        $this->set_reflection_property_value('elements', $elements);

        $this->assertStringMatchesFormatFile($file, $this->class->get_json_payload());
    }

    /**
     * Test get_json_payload() with time_to_live being present.
     *
     * @covers Lunr\Vortex\FCM\FCMPayload::get_json_payload
     */
    public function testGetJsonPayloadWithTimeToLive(): void
    {
        $file     = TEST_STATICS . '/Vortex/fcm/time_to_live.json';
        $elements = [ 'time_to_live' => 10 ];

        $this->set_reflection_property_value('elements', $elements);

        $this->assertStringMatchesFormatFile($file, $this->class->get_json_payload());
    }

    /**
     * Test get_json_payload() with everything being present.
     *
     * @covers Lunr\Vortex\FCM\FCMPayload::get_json_payload
     */
    public function testGetJsonPayload(): void
    {
        $file     = TEST_STATICS . '/Vortex/fcm/fcm.json';
        $elements = [
            'token'            => 'one',
            'collapse_key'     => 'test',
            'data'             => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
            'time_to_live'     => 10,
        ];

        $this->set_reflection_property_value('elements', $elements);

        $this->assertStringMatchesFormatFile($file, $this->class->get_json_payload());
    }

    /**
     * Test get_json_payload() with everything being present.
     *
     * @covers Lunr\Vortex\FCM\FCMPayload::get_json_payload
     */
    public function testGetJsonPayloadWithAndroid(): void
    {
        $file     = TEST_STATICS . '/Vortex/fcm/fcm_android.json';
        $elements = [
            'token'            => 'one',
            'collapse_key'     => 'test',
            'data'             => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
            'time_to_live'     => 10,
        ];

        $this->set_reflection_property_value('elements', $elements);
        $this->set_reflection_property_value('android_payload', $this->android_payload);

        $this->android_payload->expects($this->once())
                              ->method('get_payload')
                              ->willReturn([ 'notification' => 'title' ]);

        $this->assertStringMatchesFormatFile($file, $this->class->get_json_payload());
    }

    /**
     * Test get_android_payload() returns new payload.
     *
     * @covers Lunr\Vortex\FCM\FCMPayload::get_json_payload
     */
    public function testGetAndroidPayloadReturnsNewPayload(): void
    {
        $this->assertInstanceOf(FCMAndroidPayload::class, $this->class->get_android_payload());
    }

    /**
     * Test get_android_payload() returns saved payload.
     *
     * @covers Lunr\Vortex\FCM\FCMPayload::get_json_payload
     */
    public function testGetAndroidPayloadReturnsSavedPayload(): void
    {
        $this->set_reflection_property_value('android_payload', $this->android_payload);

        $this->assertSame($this->android_payload, $this->class->get_android_payload());
    }

}

?>
