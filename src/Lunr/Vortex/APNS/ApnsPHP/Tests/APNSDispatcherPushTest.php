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
use ApnsPHP\Message;
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
    public function testPushingWithUnsupportedPayloadThrowsException(object $payload): void
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

        $this->alert_payload->expects($this->once())
                            ->method('get_payload')
                            ->willReturn([ 'priority' => Priority::ConsiderPowerUsage ]);

        $result = $this->class->push($this->alert_payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\APNS\ApnsPHP\APNSResponse', $result);
    }

    /**
     * Test that push() with a non JSON payload proceeds correctly without error.
     *
     * @covers Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher::push
     */
    public function testPushWithNonJSONPayloadProceedsWithoutError(): void
    {
        $endpoints = [];

        $this->alert_payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn([ 'yo' => 'data', 'priority' => Priority::ConsiderPowerUsage  ]);

        $result = $this->class->push($this->alert_payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\APNS\ApnsPHP\APNSResponse', $result);
    }

    /**
     * Test that push() constructs the correct full payload.
     *
     * @covers Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher::push
     */
    public function testPushConstructsCorrectPayload(): void
    {
        $endpoints = [ 'endpoint1', 'endpoint2' ];

        $payload = [
            'title'             => 'title',
            'body'              => 'message',
            'thread_id'         => '59ADAE4572BF42A682F46170DA5A74EC',
            'sound'             => 'yo.mp3',
            'category'          => 'messages_for_test',
            'mutable_content'   => TRUE,
            'content_available' => TRUE,
            'topic'             => 'com.company.app',
            'priority'          => Priority::ConsiderPowerUsage,
            'collapse_key'      => 'key',
            'identifier'        => 'DF9AEF66-F39A-48C1-A5A8-69D263E21F1C',
            'yo'                => 'he',
            'badge'             => 7,
            'custom_data'       => [
                'key1' => 'value1',
                'key2' => 'value2'
            ],
        ];

        $message = new Message();
        $message->setTitle($payload['title']);
        $message->setText($payload['body']);
        $message->setThreadId($payload['thread_id']);
        $message->setSound($payload['sound']);
        $message->setCategory($payload['category']);
        $message->setMutableContent($payload['mutable_content']);
        $message->setContentAvailable($payload['content_available']);
        $message->setTopic($payload['topic']);
        $message->setPriority($payload['priority']);
        $message->setCollapseId($payload['collapse_key']);
        $message->setCustomIdentifier($payload['identifier']);
        $message->setBadge($payload['badge']);
        foreach ($payload['custom_data'] as $key => $value)
        {
            $message->setCustomProperty($key, $value);
        }

        $this->alert_payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn($payload);

        $this->apns_push->expects($this->once())
                        ->method('add')
                        ->with($message);

        $this->class->push($this->alert_payload, $endpoints);
    }

    /**
     * Test that push() log payload building error with custom property.
     *
     * @covers Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher::push
     */
    public function testPushLogPayloadBuildingCustomPropertyError(): void
    {
        $this->expectException(MessageException::class);
        $this->expectExceptionMessage('Property name \'aps\' can not be used for custom property.');
        $endpoints = [];

        $this->alert_payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn([ 'custom_data' => [ 'aps' => 'value1' ], 'priority' => Priority::ConsiderPowerUsage ]);

        $result = $this->class->push($this->alert_payload, $endpoints);

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

        $this->alert_payload->expects($this->once())
                            ->method('get_payload')
                            ->willReturn([ 'priority' => Priority::ConsiderPowerUsage ]);

        $result = $this->class->push($this->alert_payload, $endpoints);

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

        $this->alert_payload->expects($this->once())
                            ->method('get_payload')
                            ->willReturn([ 'priority' => Priority::ConsiderPowerUsage ]);

        $result = $this->class->push($this->alert_payload, $endpoints);

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

        $message = new Message();
        $message->setPriority(Priority::ConsiderPowerUsage);

        $this->apns_push->expects($this->exactly(1))
                        ->method('add')
                        ->with($message);

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

        $this->alert_payload->expects($this->once())
                            ->method('get_payload')
                            ->willReturn([ 'priority' => Priority::ConsiderPowerUsage ]);

        $result = $this->class->push($this->alert_payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\APNS\ApnsPHP\APNSResponse', $result);
    }

}

?>
