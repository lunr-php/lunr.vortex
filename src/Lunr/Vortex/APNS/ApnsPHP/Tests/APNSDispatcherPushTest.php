<?php

/**
 * This file contains the APNSDispatcherTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2016 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS\ApnsPHP\Tests;

use ApnsPHP\Exception as ApnsPHPException;
use ApnsPHP\Message\Exception as MessageException;
use ApnsPHP\Message\Priority;
use ApnsPHP\Push\Exception as PushException;
use Lunr\Vortex\Email\EmailPayload;
use Lunr\Vortex\FCM\FCMPayload;
use Lunr\Vortex\JPush\JPushMessagePayload;
use Lunr\Vortex\WNS\WNSTilePayload;

/**
 * This class contains tests for the push() method of the APNSDispatcher class.
 *
 * @covers Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher
 */
class APNSDispatcherPushTest extends APNSDispatcherTest
{

    /**
     * Unit test data provider for unsupported payload objects.
     *
     * @return array Unsupported payload objects
     */
    public static function unsupportedPayloadProvider(): array
    {
        $data          = [];
        $data['email'] = [ new EmailPayload() ];
        $data['fcm']   = [ new FCMPayload() ];
        $data['jpush'] = [ new JPushMessagePayload() ];
        $data['wns']   = [ new WNSTilePayload() ];

        return $data;
    }

    /**
     * Test that push() throws an exception is the passed payload object is not supported.
     *
     * @param object $payload Unsupported payload object
     *
     * @dataProvider unsupportedPayloadProvider
     * @covers       Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher::push
     */
    public function testPushingWithUnsupportedPayloadThrowsException($payload): void
    {
        $endpoints = [ 'endpoint' ];

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid payload object!');

        $this->class->push($payload, $endpoints);
    }

    /**
     * Test that push() returns APNSResponseObject.
     *
     * @covers Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher::push
     */
    public function testPushReturnsAPNSResponseObject(): void
    {
        $endpoints = [];

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\APNS\ApnsPHP\APNSResponse', $result);
    }

    /**
     * Test that push() resets the properties after a push.
     *
     * @covers Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher::push
     */
    public function testPushResetsProperties(): void
    {
        $endpoints = [];

        $this->class->push($this->payload, $endpoints);

        $this->assertPropertyUnset('apns_message');
    }

    /**
     * Test that push() with a non JSON payload proceeds correctly without error.
     *
     * @covers Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher::push
     */
    public function testPushWithNonJSONPayloadProceedsWithoutError(): void
    {
        $endpoints = [];

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn([ 'yo' => 'data' ]);

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\APNS\ApnsPHP\APNSResponse', $result);
    }

    /**
     * Test that push() constructs the correct full payload.
     *
     * @covers Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher::push
     */
    public function testPushConstructsCorrectPayload(): void
    {
        $endpoints = [];

        $payload = [
            'title' => 'title',
            'body' => 'message',
            'thread_id' => '59ADAE4572BF42A682F46170DA5A74EC',
            'sound' => 'yo.mp3',
            'category' => 'messages_for_test',
            'mutable_content' => TRUE,
            'content_available' => TRUE,
            'topic' => 'com.company.app',
            'priority' => Priority::ConsiderPowerUsage,
            'collapse_key' => 'key',
            'identifier' => 'identifier',
            'yo' => 'he',
            'badge' => 7,
            'custom_data' => [
                'key1' => 'value1',
                'key2' => 'value2'
            ],
        ];

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn($payload);

        $this->apns_message->expects($this->exactly(1))
                           ->method('setTitle')
                           ->with($payload['title']);

        $this->apns_message->expects($this->exactly(1))
                           ->method('setText')
                           ->with($payload['body']);

        $this->apns_message->expects($this->exactly(1))
                           ->method('setThreadID')
                           ->with($payload['thread_id']);

        $this->apns_message->expects($this->exactly(1))
                           ->method('setTopic')
                           ->with($payload['topic']);

        $this->apns_message->expects($this->exactly(1))
                           ->method('setPriority')
                           ->with($payload['priority']);

        $this->apns_message->expects($this->exactly(1))
                           ->method('setCollapseId')
                           ->with($payload['collapse_key']);

        $this->apns_message->expects($this->exactly(1))
                           ->method('setCustomIdentifier')
                           ->with($payload['identifier']);

        $this->apns_message->expects($this->exactly(1))
                           ->method('setSound')
                           ->with($payload['sound']);

        $this->apns_message->expects($this->exactly(1))
                           ->method('setCategory')
                           ->with($payload['category']);

        $this->apns_message->expects($this->exactly(1))
                           ->method('setBadge')
                           ->with($payload['badge']);

        $this->apns_message->expects($this->exactly(1))
                           ->method('setContentAvailable')
                           ->with(TRUE);

        $this->apns_message->expects($this->exactly(1))
                           ->method('setMutableContent')
                           ->with(TRUE);

        $this->apns_message->expects($this->exactly(2))
                           ->method('setCustomProperty')
                           ->withConsecutive(
                               [ 'key1', 'value1' ],
                               [ 'key2', 'value2' ]
                           );

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that push() log payload building error with badge.
     *
     * @covers Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher::push
     */
    public function testPushLogPayloadBuildingBadgeError(): void
    {
        $endpoints = [];

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn([ 'badge' => -1 ]);

        $this->apns_message->expects($this->once())
                           ->method('setBadge')
                           ->with(-1)
                           ->will($this->throwException(new MessageException('Invalid badge: -1')));

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with('Invalid badge: -1');

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\APNS\ApnsPHP\APNSResponse', $result);
    }

    /**
     * Test that push() log payload building error with custom property.
     *
     * @covers Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher::push
     */
    public function testPushLogPayloadBuildingCustomPropertyError(): void
    {
        $endpoints = [];

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn([ 'custom_data' => [ 'apns' => 'value1' ]]);

        $this->apns_message->expects($this->once())
                           ->method('setCustomProperty')
                           ->with('apns', 'value1')
                           ->will($this->throwException(new MessageException('Reserved keyword: apns')));

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with('Reserved keyword: apns');

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\APNS\ApnsPHP\APNSResponse', $result);
    }

    /**
     * Test that push() add all endpoints to the message.
     *
     * @covers Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher::push
     */
    public function testPushAddAllEndpointsToMessage(): void
    {
        $endpoints = [ 'endpoint1', 'endpoint2' ];

        $this->apns_message->expects($this->exactly(2))
                           ->method('addRecipient')
                           ->withConsecutive([ 'endpoint1' ], [ 'endpoint2' ]);

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that push() log invalid endpoints.
     *
     * @covers Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher::push
     */
    public function testPushLogInvalidEndpoints(): void
    {
        $endpoints = [ 'endpoint1', 'endpoint2', 'endpoint3' ];

        $pos = 0;

        $this->apns_message->expects($this->exactly(3))
                           ->method('addRecipient')
                           ->withConsecutive([ 'endpoint1' ], [ 'endpoint2' ], [ 'endpoint3' ])
                           ->willReturnOnConsecutiveCalls(
                               $this->throwException(new MessageException('Invalid endpoint: endpoint1')),
                               NULL,
                               $this->throwException(new MessageException('Invalid endpoint: endpoint3'))
                           );

        $this->logger->expects($this->exactly(2))
                     ->method('warning')
                     ->withConsecutive(
                        [ 'Invalid endpoint: endpoint1' ],
                        [ 'Invalid endpoint: endpoint3' ]
                     );

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\APNS\ApnsPHP\APNSResponse', $result);
    }

    /**
     * Test that push() log failed connection.
     *
     * @covers Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher::push
     */
    public function testPushLogFailedConnection(): void
    {
        $endpoints = [];

        $this->apns_push->expects($this->once())
                        ->method('connect')
                        ->will($this->throwException(new ApnsPHPException('Failed to connect')));

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                        'Dispatching APNS notification failed: {error}',
                        [ 'error' => 'Failed to connect' ]
                     );

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\APNS\ApnsPHP\APNSResponse', $result);
    }

    /**
     * Test that push() log failed sending.
     *
     * @covers Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher::push
     */
    public function testPushLogFailedSending(): void
    {
        $endpoints = [];

        $this->apns_push->expects($this->once())
                        ->method('send')
                        ->will($this->throwException(new PushException('Failed to send')));

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                        'Dispatching APNS notification failed: {error}',
                        [ 'error' => 'Failed to send' ]
                     );

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\APNS\ApnsPHP\APNSResponse', $result);
    }

    /**
     * Test that push() send successfully.
     *
     * @covers Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher::push
     */
    public function testPushSuccess(): void
    {
        $endpoints = [];

        $pos = 0;

        $this->apns_push->expects($this->exactly(1))
                        ->method('add')
                        ->with($this->apns_message);

        $this->apns_push->expects($this->exactly(1))
                        ->method('connect');

        $this->apns_push->expects($this->exactly(1))
                        ->method('send');

        $this->apns_push->expects($this->exactly(1))
                        ->method('disconnect');

        $error = [
            [
                'MESSAGE' => 'Error',
                'ERRORS'  => [],
            ],
        ];

        $this->apns_push->expects($this->exactly(1))
                        ->method('getErrors')
                        ->willReturn($error);

        $this->logger->expects($this->never())
                     ->method('warning');

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\APNS\ApnsPHP\APNSResponse', $result);
    }

}

?>
