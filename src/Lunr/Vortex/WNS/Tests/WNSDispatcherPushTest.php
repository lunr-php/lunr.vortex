<?php

/**
 * This file contains the WNSDispatcherPushTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\WNS\Tests;

use Lunr\Vortex\APNS\APNSAlertPayload;
use Lunr\Vortex\Email\EmailPayload;
use Lunr\Vortex\FCM\FCMPayload;
use Lunr\Vortex\JPush\JPushMessagePayload;
use Lunr\Vortex\WNS\WNSType;
use WpOrg\Requests\Exception as RequestsException;

/**
 * This class contains test for the push() method of the WNSDispatcher class.
 *
 * @covers Lunr\Vortex\WNS\WNSDispatcher
 */
class WNSDispatcherPushTest extends WNSDispatcherTest
{

    /**
     * Unit test data provider for unsupported payload objects.
     *
     * @return array Unsupported payload objects
     */
    public static function unsupportedPayloadProvider(): array
    {
        $data          = [];
        $data['apns']  = [ new APNSAlertPayload() ];
        $data['email'] = [ new EmailPayload() ];
        $data['fcm']   = [ new FCMPayload() ];
        $data['jpush'] = [ new JPushMessagePayload() ];

        return $data;
    }

    /**
     * Test that push() throws an exception is the passed payload object is not supported.
     *
     * @param object $payload Unsupported payload object
     *
     * @dataProvider unsupportedPayloadProvider
     * @covers       Lunr\Vortex\WNS\WNSDispatcher::push
     */
    public function testPushingWithUnsupportedPayloadThrowsException($payload): void
    {
        $endpoints = [ 'endpoint' ];

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid payload object!');

        $this->class->push($payload, $endpoints);
    }

    /**
     * Test that the response will be null if no authentication is done.
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::push
     */
    public function testPushingWithoutOauthReturnsWNSResponse(): void
    {
        $endpoints = [ 'endpoint' ];

        $message = 'Tried to push WNS notification to {endpoint} but wasn\'t authenticated.';
        $context = [ 'endpoint' => 'endpoint' ];

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with($message, $context);

        $this->http->expects($this->never())
                   ->method('post');

        $this->assertInstanceOf('\Lunr\Vortex\WNS\WNSResponse', $this->class->push($this->payload, $endpoints));
    }

    /**
     * Test that push() resets the properties after a push.
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::push
     */
    public function testPushWithoutOauthResetsProperties(): void
    {
        $this->set_reflection_property_value('type', WNSType::TOAST);

        $endpoints = [ 'endpoint' ];

        $message = 'Tried to push WNS notification to {endpoint} but wasn\'t authenticated.';
        $context = [ 'endpoint' => 'endpoint' ];

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with($message, $context);

        $this->http->expects($this->never())
                   ->method('post');

        $this->class->push($this->payload, $endpoints);

        $this->assertPropertySame('type', WNSType::RAW);
    }

    /**
     * Test that pushing a Tile notification sets the X-WNS-Type header.
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::push
     */
    public function testPushingTileSetsTargetHeader(): void
    {
        $this->payload = $this->getMockBuilder('Lunr\Vortex\WNS\WNSTilePayload')
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->set_reflection_property_value('oauth_token', '123456');

        $endpoints = [ 'endpoint' ];

        $headers = [
            'X-WNS-Type'             => 'wns/tile',
            'Accept'                 => 'application/*',
            'Authorization'          => 'Bearer 123456',
            'X-WNS-RequestForStatus' => 'true',
            'Content-Type'           => 'text/xml',
        ];

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn('payload');

        $this->http->expects($this->once())
                   ->method('post')
                   ->with('endpoint', $headers, 'payload')
                   ->willReturn($this->response);

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that pushing a Toast notification sets the X-WNS-Type header.
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::push
     */
    public function testPushingToastSetsTargetHeader(): void
    {
        $this->payload = $this->getMockBuilder('Lunr\Vortex\WNS\WNSToastPayload')
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->set_reflection_property_value('oauth_token', '123456');

        $endpoints = [ 'endpoint' ];

        $headers = [
            'X-WNS-Type'             => 'wns/toast',
            'Accept'                 => 'application/*',
            'Authorization'          => 'Bearer 123456',
            'X-WNS-RequestForStatus' => 'true',
            'Content-Type'           => 'text/xml',
        ];

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn('payload');

        $this->http->expects($this->once())
                   ->method('post')
                   ->with('endpoint', $headers, 'payload')
                   ->willReturn($this->response);

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that pushing a Raw notification does not set the X-WNS-Type header.
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::push
     */
    public function testPushingRawDoesNotSetTargetHeader(): void
    {
        $this->set_reflection_property_value('oauth_token', '123456');

        $endpoints = [ 'endpoint' ];

        $headers = [
            'X-WNS-Type'             => 'wns/raw',
            'Accept'                 => 'application/*',
            'Authorization'          => 'Bearer 123456',
            'X-WNS-RequestForStatus' => 'true',
            'Content-Type'           => 'application/octet-stream',
        ];

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn('payload');

        $this->http->expects($this->once())
                   ->method('post')
                   ->with('endpoint', $headers, 'payload')
                   ->willReturn($this->response);

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that push() returns WNSResponseObject.
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::push
     */
    public function testPushReturnsWNSResponseObjectOnRequestFailure(): void
    {
        $this->set_reflection_property_value('oauth_token', '123456');

        $endpoints = [ 'endpoint' ];

        $headers = [
            'X-WNS-Type'             => 'wns/raw',
            'Accept'                 => 'application/*',
            'Authorization'          => 'Bearer 123456',
            'X-WNS-RequestForStatus' => 'true',
            'Content-Type'           => 'application/octet-stream',
        ];

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn('payload');

        $this->http->expects($this->once())
                   ->method('post')
                   ->with('endpoint', $headers, 'payload')
                   ->will($this->throwException(new RequestsException('Network error!', 'curlerror', NULL)));

        $message = 'Dispatching WNS notification to {endpoint} failed: {error}';
        $context = [ 'endpoint' => 'endpoint', 'error' => 'Network error!' ];

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with($message, $context);

        $this->assertInstanceOf('Lunr\Vortex\WNS\WNSResponse', $this->class->push($this->payload, $endpoints));
    }

    /**
     * Test that push() returns WNSResponseObject.
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::push
     */
    public function testPushReturnsWNSResponseObject(): void
    {
        $this->set_reflection_property_value('oauth_token', '123456');

        $endpoints = [ 'endpoint' ];

        $headers = [
            'X-WNS-Type'             => 'wns/raw',
            'Accept'                 => 'application/*',
            'Authorization'          => 'Bearer 123456',
            'X-WNS-RequestForStatus' => 'true',
            'Content-Type'           => 'application/octet-stream',
        ];

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn('payload');

        $this->http->expects($this->once())
                   ->method('post')
                   ->with('endpoint', $headers, 'payload')
                   ->willReturn($this->response);

        $this->assertInstanceOf('Lunr\Vortex\WNS\WNSResponse', $this->class->push($this->payload, $endpoints));
    }

    /**
     * Test that push() resets the properties after a push.
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::push
     */
    public function testPushResetsPropertiesOnRequestFailure(): void
    {
        $this->payload = $this->getMockBuilder('Lunr\Vortex\WNS\WNSToastPayload')
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->set_reflection_property_value('oauth_token', '123456');

        $endpoints = [ 'endpoint' ];

        $headers = [
            'X-WNS-Type'             => 'wns/toast',
            'Accept'                 => 'application/*',
            'Authorization'          => 'Bearer 123456',
            'X-WNS-RequestForStatus' => 'true',
            'Content-Type'           => 'text/xml',
        ];

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn('payload');

        $this->http->expects($this->once())
                   ->method('post')
                   ->with('endpoint', $headers, 'payload')
                   ->will($this->throwException(new RequestsException('Network error!', 'curlerror', NULL)));

        $message = 'Dispatching WNS notification to {endpoint} failed: {error}';
        $context = [ 'endpoint' => 'endpoint', 'error' => 'Network error!' ];

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with($message, $context);

        $this->class->push($this->payload, $endpoints);
        $this->assertPropertySame('type', WNSType::RAW);
    }

    /**
     * Test that push() resets the properties after a push.
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::push
     */
    public function testPushResetsProperties(): void
    {
        $this->payload = $this->getMockBuilder('Lunr\Vortex\WNS\WNSToastPayload')
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->set_reflection_property_value('oauth_token', '123456');

        $endpoints = [ 'endpoint' ];

        $headers = [
            'X-WNS-Type'             => 'wns/toast',
            'Accept'                 => 'application/*',
            'Authorization'          => 'Bearer 123456',
            'X-WNS-RequestForStatus' => 'true',
            'Content-Type'           => 'text/xml',
        ];

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn('payload');

        $this->http->expects($this->once())
                   ->method('post')
                   ->with('endpoint', $headers, 'payload')
                   ->willReturn($this->response);

        $this->class->push($this->payload, $endpoints);

        $this->assertPropertySame('type', WNSType::RAW);
    }

}

?>
