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
use Lunr\Vortex\FCM\FCMApnsPayload;

/**
 * This class contains tests for the getters of the FCMPayload class.
 *
 * @covers Lunr\Vortex\FCM\FCMPayload
 */
class FCMPayloadGetTest extends FCMPayloadTestCase
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

        $this->setReflectionPropertyValue('elements', $elements);

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

        $this->setReflectionPropertyValue('elements', $elements);

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

        $this->setReflectionPropertyValue('elements', $elements);

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

        $this->setReflectionPropertyValue('elements', $elements);

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

        $this->setReflectionPropertyValue('elements', $elements);
        $this->setReflectionPropertyValue('androidPayload', $this->androidPayload);

        $this->androidPayload->expects($this->once())
                             ->method('get_payload')
                             ->willReturn([ 'notification' => 'title' ]);

        $this->assertStringMatchesFormatFile($file, $this->class->get_json_payload());
    }

    /**
     * Test get_json_payload() with everything being present.
     *
     * @covers Lunr\Vortex\FCM\FCMPayload::get_json_payload
     */
    public function testGetJsonPayloadWithApns(): void
    {
        $file     = TEST_STATICS . '/Vortex/fcm/fcm_apns.json';
        $elements = [
            'token'            => 'one',
            'collapse_key'     => 'test',
            'data'             => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
            'time_to_live'     => 10,
        ];

        $this->setReflectionPropertyValue('elements', $elements);
        $this->setReflectionPropertyValue('apnsPayload', $this->apnsPayload);

        $this->apnsPayload->expects($this->once())
                          ->method('get_payload')
                          ->willReturn([ 'notification' => 'title' ]);

        $this->assertStringMatchesFormatFile($file, $this->class->get_json_payload());
    }

    /**
     * Test android_payload() returns new payload.
     *
     * @covers Lunr\Vortex\FCM\FCMPayload::android_payload
     */
    public function testAndroidPayloadReturnsNewPayload(): void
    {
        $return = $this->class->android_payload();

        $this->assertInstanceOf(FCMAndroidPayload::class, $return);

        $this->assertPropertySame('androidPayload', $return);
    }

    /**
     * Test android_payload() returns saved payload.
     *
     * @covers Lunr\Vortex\FCM\FCMPayload::android_payload
     */
    public function testAndroidPayloadReturnsSavedPayload(): void
    {
        $this->setReflectionPropertyValue('androidPayload', $this->androidPayload);

        $this->assertSame($this->androidPayload, $this->class->android_payload());
    }

    /**
     * Test apns_payload() returns new payload.
     *
     * @covers Lunr\Vortex\FCM\FCMPayload::apns_payload
     */
    public function testGetApnsPayloadReturnsNewPayload(): void
    {
        $return = $this->class->apns_payload();

        $this->assertInstanceOf(FCMApnsPayload::class, $return);

        $this->assertPropertySame('apnsPayload', $return);
    }

    /**
     * Test apns_payload() returns saved payload.
     *
     * @covers Lunr\Vortex\FCM\FCMPayload::apns_payload
     */
    public function testGetApnsPayloadReturnsSavedPayload(): void
    {
        $this->setReflectionPropertyValue('apnsPayload', $this->apnsPayload);

        $this->assertSame($this->apnsPayload, $this->class->apns_payload());
    }

}

?>
