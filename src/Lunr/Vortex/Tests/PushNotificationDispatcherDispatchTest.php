<?php

/**
 * This file contains the PushNotificationDispatcherDispatchTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\Tests;

use Lunr\Vortex\APNS\APNSPayload;
use Lunr\Vortex\Email\EmailPayload;
use Lunr\Vortex\FCM\FCMPayload;
use Lunr\Vortex\JPush\JPushMessagePayload;
use Lunr\Vortex\PushNotificationStatus;
use Lunr\Vortex\WNS\WNSTilePayload;

/**
 * This class contains tests for the dispatch function of the PushNotificationDispatcher class.
 *
 * @covers Lunr\Vortex\PushNotificationDispatcher
 */
class PushNotificationDispatcherDispatchTest extends PushNotificationDispatcherTestCase
{

    /**
     * Test that dispatch doesn't do any push in case of no endpoint.
     *
     * @covers Lunr\Vortex\PushNotificationDispatcher::dispatch
     */
    public function testDispatchDoesNoPushIfNoEndpoint(): void
    {
        $apnsPayload = $this->getMockBuilder(APNSPayload::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $dataPayload = $this->getMockBuilder(FCMPayload::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $endpoints = [];
        $payloads  = [
            'apns' => [ 'apns' => $apnsPayload ],
            'fcm'  => [ 'data' => $dataPayload ],
        ];

        $this->apns->expects($this->never())
                   ->method('push');

        $this->fcm->expects($this->never())
                  ->method('push');

        $this->class->dispatch($endpoints, $payloads);

        $emptyStatuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $this->assertPropertySame('statuses', $emptyStatuses);
    }

    /**
     * Test that dispatch doesn't do any push in case of no payload.
     *
     * @covers Lunr\Vortex\PushNotificationDispatcher::dispatch
     */
    public function testDispatchDoesNoPushIfNoPayload(): void
    {
        $endpoints = [
            [
                'endpoint'    => 'abcde-12345',
                'platform'    => 'apns',
                'payloadType' => 'apns',
                'client'      => 'iOS',
                'language'    => 'en-US',
            ],
            [
                'endpoint'    => 'fghij-67890',
                'platform'    => 'fcm',
                'payloadType' => 'data',
                'client'      => 'Android',
                'language'    => 'en-US',
            ],
        ];

        $payloads         = [];
        $expectedStatuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $expectedStatuses[PushNotificationStatus::NotHandled->value] = [
            [
                'endpoint'    => 'abcde-12345',
                'platform'    => 'apns',
                'payloadType' => 'apns',
                'client'      => 'iOS',
                'language'    => 'en-US',
            ],
            [
                'endpoint'    => 'fghij-67890',
                'platform'    => 'fcm',
                'payloadType' => 'data',
                'client'      => 'Android',
                'language'    => 'en-US',
            ],
        ];

        $this->apns->expects($this->never())
                   ->method('push');

        $this->fcm->expects($this->never())
                  ->method('push');

        $this->class->dispatch($endpoints, $payloads);

        $this->assertPropertySame('statuses', $expectedStatuses);
    }

    /**
     * Test that dispatch doesn't do any push in case of no endpoint for the payloads defined.
     *
     * @covers Lunr\Vortex\PushNotificationDispatcher::dispatch
     */
    public function testDispatchDoesNoPushIfNoEndpointsForPayloads(): void
    {
        $wnsPayload = $this->getMockBuilder(WNSTilePayload::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $emailPayload = $this->getMockBuilder(EmailPayload::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $endpoints = [
            [
                'endpoint'    => 'abcde-12345',
                'platform'    => 'apns',
                'payloadType' => 'apns',
                'client'      => 'iOS',
                'language'    => 'en-US',
            ],
            [
                'endpoint'    => 'fghij-67890',
                'platform'    => 'fcm',
                'payloadType' => 'data',
                'client'      => 'Android',
                'language'    => 'en-US',
            ],
        ];

        $payloads = [
            'wns'   => [ 'tile'  => $wnsPayload ],
            'email' => [ 'email' => $emailPayload ],
        ];

        $expectedStatuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $expectedStatuses[PushNotificationStatus::NotHandled->value] = [
            [
                'endpoint'    => 'abcde-12345',
                'platform'    => 'apns',
                'payloadType' => 'apns',
                'client'      => 'iOS',
                'language'    => 'en-US',
            ],
            [
                'endpoint'    => 'fghij-67890',
                'platform'    => 'fcm',
                'payloadType' => 'data',
                'client'      => 'Android',
                'language'    => 'en-US',
            ],
        ];

        $this->apns->expects($this->never())
                   ->method('push');

        $this->email->expects($this->never())
                    ->method('push');

        $this->fcm->expects($this->never())
                  ->method('push');

        $this->wns->expects($this->never())
                   ->method('push');

        $this->class->dispatch($endpoints, $payloads);

        $this->assertPropertySame('statuses', $expectedStatuses);
    }

    /**
     * Test that dispatch doesn't do any push in case of no endpoint for the payloads defined.
     *
     * @covers Lunr\Vortex\PushNotificationDispatcher::dispatch
     */
    public function testDispatchDoesNoPushIfNoEndpointsForPayloadType(): void
    {
        $dispatchers = [
            'apns'  => $this->apns,
            'fcm'   => $this->fcm,
        ];

        $this->setReflectionPropertyValue('dispatchers', $dispatchers);

        $apnsPayload = $this->getMockBuilder(APNSPayload::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $fcmPayload = $this->getMockBuilder(FCMPayload::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $endpoints = [
            [
                'endpoint'    => 'abcde-12345',
                'platform'    => 'apns',
                'payloadType' => 'apns',
                'client'      => 'iOS',
                'language'    => 'en-US',
            ],
            [
                'endpoint'    => 'fghij-67890',
                'platform'    => 'fcm',
                'payloadType' => 'data',
                'client'      => 'Android',
                'language'    => 'en-US',
            ],
        ];

        $payloads = [
            'apns' => [ 'notification' => $apnsPayload ],
            'fcm'  => [ 'notification' => $fcmPayload ],
        ];

        $expectedStatuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $expectedStatuses[PushNotificationStatus::NotHandled->value] = [
            [
                'endpoint'    => 'abcde-12345',
                'platform'    => 'apns',
                'payloadType' => 'apns',
                'client'      => 'iOS',
                'language'    => 'en-US',
            ],
            [
                'endpoint'    => 'fghij-67890',
                'platform'    => 'fcm',
                'payloadType' => 'data',
                'client'      => 'Android',
                'language'    => 'en-US',
            ],
        ];

        $this->apns->expects($this->never())
                   ->method('push');

        $this->email->expects($this->never())
                    ->method('push');

        $this->fcm->expects($this->never())
                  ->method('push');

        $this->wns->expects($this->never())
                   ->method('push');

        $this->class->dispatch($endpoints, $payloads);

        $this->assertPropertySame('statuses', $expectedStatuses);
    }

    /**
     * Test that dispatch doesn't do any push in case of no endpoint for the payloads defined.
     *
     * @covers Lunr\Vortex\PushNotificationDispatcher::dispatch
     */
    public function testDispatchDoesNoPushIfNoEndpointsForPayloadTypeAndNoDispatcher(): void
    {
        $apnsPayload = $this->getMockBuilder(APNSPayload::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $fcmPayload = $this->getMockBuilder(FCMPayload::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $endpoints = [
            [
                'endpoint'    => 'abcde-12345',
                'platform'    => 'apns',
                'payloadType' => 'apns',
                'client'      => 'iOS',
                'language'    => 'en-US',
            ],
            [
                'endpoint'    => 'fghij-67890',
                'platform'    => 'fcm',
                'payloadType' => 'data',
                'client'      => 'Android',
                'language'    => 'en-US',
            ],
        ];

        $payloads = [
            'apns' => [ 'notification' => $apnsPayload ],
            'fcm'  => [ 'notification' => $fcmPayload ],
        ];

        $expectedStatuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $expectedStatuses[PushNotificationStatus::NotHandled->value] = [
            [
                'endpoint'    => 'abcde-12345',
                'platform'    => 'apns',
                'payloadType' => 'apns',
                'client'      => 'iOS',
                'language'    => 'en-US',
            ],
            [
                'endpoint'    => 'fghij-67890',
                'platform'    => 'fcm',
                'payloadType' => 'data',
                'client'      => 'Android',
                'language'    => 'en-US',
            ],
        ];

        $this->apns->expects($this->never())
                   ->method('push');

        $this->email->expects($this->never())
                    ->method('push');

        $this->fcm->expects($this->never())
                  ->method('push');

        $this->wns->expects($this->never())
                   ->method('push');

        $this->class->dispatch($endpoints, $payloads);

        $this->assertPropertySame('statuses', $expectedStatuses);
    }

    /**
     * Test that dispatch doesn't do any push in case of no dispatcher for the payloads defined.
     *
     * @covers Lunr\Vortex\PushNotificationDispatcher::dispatch
     */
    public function testDispatchDoesNoPushIfNoDispatcherForPayloads(): void
    {
        $dispatchers = [
            'fcm'   => $this->fcm,
            'email' => $this->email,
        ];
        $this->setReflectionPropertyValue('dispatchers', $dispatchers);

        $apnsPayload = $this->getMockBuilder(APNSPayload::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $dataPayload = $this->getMockBuilder(FCMPayload::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $emailPayload = $this->getMockBuilder(EmailPayload::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $endpoints = [
            [
                'endpoint'    => 'abcde-12345',
                'platform'    => 'apns',
                'payloadType' => 'apns',
                'client'      => 'iOS',
                'language'    => 'en-US',
            ],
            [
                'endpoint'    => 'fghij-67890',
                'platform'    => 'fcm',
                'payloadType' => 'data',
                'client'      => 'Android',
                'language'    => 'en-US',
            ],
        ];

        $payloads = [
            'apns'  => [ 'apns' => $apnsPayload ],
            'email' => [ 'email' => $emailPayload ],
            'fcm'   => [ 'data' => $dataPayload ],
        ];

        $expectedStatuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $expectedStatuses[PushNotificationStatus::Success->value] = [
            [
                'endpoint'    => 'fghij-67890',
                'platform'    => 'fcm',
                'payloadType' => 'data',
                'client'      => 'Android',
                'language'    => 'en-US',
            ],
        ];

        $expectedStatuses[PushNotificationStatus::NotHandled->value] = [
            [
                'endpoint'    => 'abcde-12345',
                'platform'    => 'apns',
                'payloadType' => 'apns',
                'client'      => 'iOS',
                'language'    => 'en-US',
            ],
        ];

        $this->fcm->expects($this->once())
                   ->method('push')
                   ->with($dataPayload, [ 'fghij-67890' ])
                   ->willReturn($this->fcmResponse);

        $this->fcmResponse->expects($this->once())
                          ->method('get_status')
                          ->with('fghij-67890')
                          ->willReturn(PushNotificationStatus::Success);

        $this->apns->expects($this->never())
                   ->method('push');

        $this->class->dispatch($endpoints, $payloads);

        $this->assertPropertySame('statuses', $expectedStatuses);
    }

    /**
     * Test dispatch send correct payload to each endpoint.
     *
     * @covers Lunr\Vortex\PushNotificationDispatcher::dispatch
     */
    public function testDispatchSendsCorrectPayloadsToDifferentEndpoints(): void
    {
        $dispatchers = [
            'apns'  => $this->apns,
            'fcm'   => $this->fcm,
            'email' => $this->email,
        ];
        $this->setReflectionPropertyValue('dispatchers', $dispatchers);

        $apnsPayload = $this->getMockBuilder(APNSPayload::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $dataPayload = $this->getMockBuilder(FCMPayload::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $emailPayload = $this->getMockBuilder(EmailPayload::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $endpoints = [
            [
                'endpoint'    => 'abcde-12345',
                'platform'    => 'apns',
                'payloadType' => 'apns',
                'client'      => 'iOS',
                'language'    => 'en-US',
            ],
            [
                'endpoint'    => 'fghij-67890',
                'platform'    => 'fcm',
                'payloadType' => 'data',
                'client'      => 'Android',
                'language'    => 'en-US',
            ],
        ];

        $payloads = [
            'apns'  => [ 'apns' => $apnsPayload ],
            'email' => [ 'email' => $emailPayload ],
            'fcm'   => [ 'data' => $dataPayload ],
        ];

        $this->fcm->expects($this->once())
                   ->method('push')
                   ->with($dataPayload, [ 'fghij-67890' ])
                   ->willReturn($this->fcmResponse);

        $this->apns->expects($this->once())
                   ->method('push')
                   ->with($apnsPayload, [ 'abcde-12345' ])
                   ->willReturn($this->apnsResponse);

        $this->fcmResponse->expects($this->once())
                          ->method('get_status')
                          ->willReturn(PushNotificationStatus::Success);

        $this->apnsResponse->expects('get_status')
                           ->once()
                           ->andReturn(PushNotificationStatus::Success);

        $this->class->dispatch($endpoints, $payloads);

        $property    = $this->getReflectionProperty('dispatchers');
        $dispatchers = $property->getValue($this->class);

        $this->assertArrayHasKey('apns', $dispatchers);
        $this->assertArrayHasKey('fcm', $dispatchers);
    }

    /**
     * Test that dispatch push endpoints one by one for dispatcher that don't support multicast.
     *
     * @covers Lunr\Vortex\PushNotificationDispatcher::dispatch
     */
    public function testDispatchSinglePushOneByOne(): void
    {
        $this->setReflectionPropertyValue('dispatchers', [ 'wns' => $this->wns ]);

        $tilePayload = $this->getMockBuilder(WNSTilePayload::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $endpoints = [
            [
                'endpoint'    => 'abcde-12345',
                'platform'    => 'wns',
                'payloadType' => 'tile',
                'client'      => 'Blackberry',
                'language'    => 'en-US',
            ],
            [
                'endpoint'    => 'abcde-56789',
                'platform'    => 'wns',
                'payloadType' => 'tile',
                'client'      => 'Blackberry',
                'language'    => 'en-US',
            ],
        ];

        $payloads = [
            'wns' => [ 'tile' => $tilePayload ],
        ];

        $expectedStatuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $expectedStatuses[PushNotificationStatus::Success->value] = [
            [
                'endpoint'    => 'abcde-12345',
                'platform'    => 'wns',
                'payloadType' => 'tile',
                'client'      => 'Blackberry',
                'language'    => 'en-US',
            ],
        ];

        $expectedStatuses[PushNotificationStatus::Error->value] = [
            [
                'endpoint'    => 'abcde-56789',
                'platform'    => 'wns',
                'payloadType' => 'tile',
                'client'      => 'Blackberry',
                'language'    => 'en-US',
            ],
        ];

        $this->wns->expects($this->exactly(2))
                  ->method('push')
                  ->willReturn($this->wnsResponse);

        $this->wnsResponse->expects('get_status')
                          ->once()
                          ->with('abcde-12345')
                          ->andReturn(PushNotificationStatus::Success);

        $this->wnsResponse->expects('get_status')
                          ->once()
                          ->with('abcde-56789')
                          ->andReturn(PushNotificationStatus::Error);

        $this->class->dispatch($endpoints, $payloads);

        $this->assertPropertySame('statuses', $expectedStatuses);
    }

    /**
     * Test that dispatch push endpoints all at once for dispatcher that support multicast.
     *
     * @covers Lunr\Vortex\PushNotificationDispatcher::dispatch
     */
    public function testDispatchSinglePushAllAtOnce(): void
    {
        $dispatchers = [
            'apns'  => $this->apns,
            'fcm'   => $this->fcm,
            'email' => $this->email,
        ];
        $this->setReflectionPropertyValue('dispatchers', $dispatchers);

        $apnsPayload = $this->getMockBuilder(APNSPayload::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $endpoints = [
            [
                'endpoint'    => 'endpoint1',
                'platform'    => 'apns',
                'payloadType' => 'apns',
                'client'      => 'iOS',
                'language'    => 'en-US',
            ],
            [
                'endpoint'    => 'endpoint2',
                'platform'    => 'apns',
                'payloadType' => 'apns',
                'client'      => 'iOS',
                'language'    => 'en-US',
            ],
        ];

        $payloads = [
            'apns' => [ 'apns' => $apnsPayload ],
        ];

        $expectedStatuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $expectedStatuses[PushNotificationStatus::Success->value] = [
            [
                'endpoint'    => 'endpoint1',
                'platform'    => 'apns',
                'payloadType' => 'apns',
                'client'      => 'iOS',
                'language'    => 'en-US',
            ],
        ];

        $expectedStatuses[PushNotificationStatus::Error->value] = [
            [
                'endpoint'    => 'endpoint2',
                'platform'    => 'apns',
                'payloadType' => 'apns',
                'client'      => 'iOS',
                'language'    => 'en-US',
            ],
        ];

        $this->apns->expects($this->once())
                   ->method('push')
                   ->with($apnsPayload, [ 'endpoint1', 'endpoint2' ])
                   ->willReturn($this->apnsResponse);

        $this->apnsResponse->expects('get_status')
                           ->once()
                           ->with('endpoint1')
                           ->andReturn(PushNotificationStatus::Success);

        $this->apnsResponse->expects('get_status')
                           ->once()
                           ->with('endpoint2')
                           ->andReturn(PushNotificationStatus::Error);

        $this->class->dispatch($endpoints, $payloads);

        $this->assertPropertySame('statuses', $expectedStatuses);
    }

    /**
     * Test dispatch marks endpoints without generated payload as not handled..
     *
     * @covers Lunr\Vortex\PushNotificationDispatcher::dispatch
     */
    public function testDispatchMarksEndpointsWithoutPayloadsAsNotHandled(): void
    {
        $dispatchers = [
            'apns'  => $this->apns,
            'fcm'   => $this->fcm,
            'email' => $this->email,
        ];
        $this->setReflectionPropertyValue('dispatchers', $dispatchers);

        $apnsPayload = $this->getMockBuilder(APNSPayload::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $emailPayload = $this->getMockBuilder(EmailPayload::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $endpoints = [
            [
                'endpoint'    => 'abcde-12345',
                'platform'    => 'apns',
                'payloadType' => 'apns',
                'client'      => 'iOS',
                'language'    => 'en-US',
            ],
            [
                'endpoint'    => 'fghij-67890',
                'platform'    => 'fcm',
                'payloadType' => 'data',
                'client'      => 'Android',
                'language'    => 'en-US',
            ],
        ];

        $payloads = [
            'apns'  => [ 'apns' => $apnsPayload ],
            'email' => [ 'email' => $emailPayload ],
        ];

        $expectedStatuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $expectedStatuses[PushNotificationStatus::Success->value] = [
            [
                'endpoint'    => 'abcde-12345',
                'platform'    => 'apns',
                'payloadType' => 'apns',
                'client'      => 'iOS',
                'language'    => 'en-US',
            ],
        ];

        $expectedStatuses[PushNotificationStatus::NotHandled->value] = [
            [
                'endpoint'    => 'fghij-67890',
                'platform'    => 'fcm',
                'payloadType' => 'data',
                'client'      => 'Android',
                'language'    => 'en-US',
            ],
        ];

        $this->apns->expects($this->once())
                   ->method('push')
                   ->with($apnsPayload, [ 'abcde-12345' ])
                   ->willReturn($this->apnsResponse);

        $this->apnsResponse->expects('get_status')
                            ->once()
                            ->with('abcde-12345')
                            ->andReturn(PushNotificationStatus::Success);

        $this->class->dispatch($endpoints, $payloads);

        $property    = $this->getReflectionProperty('dispatchers');
        $dispatchers = $property->getValue($this->class);

        $this->assertArrayHasKey('apns', $dispatchers);
        $this->assertArrayHasKey('fcm', $dispatchers);

        $this->assertPropertySame('statuses', $expectedStatuses);
    }

    /**
     * Test dispatch with multi cast and get deferred response batches
     *
     * @covers Lunr\Vortex\PushNotificationDispatcher::dispatch
     */
    public function testDispatchMultiCastWithDeferredResponse(): void
    {
        $this->setReflectionPropertyValue('dispatchers', [ 'jpush' => $this->jpush ]);

        $jpushPayload = $this->getMockBuilder(JPushMessagePayload::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $endpoints = [
            [
                'endpoint'    => 'abcde-12345',
                'platform'    => 'jpush',
                'payloadType' => 'notification',
                'client'      => 'Android',
                'language'    => 'en-US',
            ],
            [
                'endpoint'    => 'fghij-67890',
                'platform'    => 'jpush',
                'payloadType' => 'notification',
                'client'      => 'Android',
                'language'    => 'en-US',
            ],
            [
                'endpoint'    => 'endpoint1',
                'platform'    => 'jpush',
                'payloadType' => 'notification',
                'client'      => 'Android',
                'language'    => 'en-US',
            ],
        ];

        $expectedStatuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $expectedStatuses[PushNotificationStatus::Success->value] = [
            [
                'endpoint'    => 'endpoint1',
                'platform'    => 'jpush',
                'payloadType' => 'notification',
                'client'      => 'Android',
                'language'    => 'en-US',
            ],
        ];

        $expectedStatuses[PushNotificationStatus::Deferred->value] = [
            [
                'endpoint'    => 'abcde-12345',
                'platform'    => 'jpush',
                'payloadType' => 'notification',
                'client'      => 'Android',
                'language'    => 'en-US',
                'message_id'  => '165465645',
            ],
            [
                'endpoint'    => 'fghij-67890',
                'platform'    => 'jpush',
                'payloadType' => 'notification',
                'client'      => 'Android',
                'language'    => 'en-US',
                'message_id'  => '555165655',
            ],
        ];

        $this->jpush->expects($this->once())
                    ->method('push')
                    ->with($jpushPayload, [ 'abcde-12345', 'fghij-67890', 'endpoint1' ])
                    ->willReturn($this->jpushResponse);

        $this->jpushResponse->expects('get_status')
                             ->once()
                             ->with('abcde-12345')
                             ->andReturn(PushNotificationStatus::Deferred);

        $this->jpushResponse->expects('get_status')
                             ->once()
                             ->with('fghij-67890')
                             ->andReturn(PushNotificationStatus::Deferred);

        $this->jpushResponse->expects('get_status')
                             ->once()
                             ->with('endpoint1')
                             ->andReturn(PushNotificationStatus::Success);

        $this->jpushResponse->expects('get_message_id')
                             ->once()
                             ->with('abcde-12345')
                             ->andReturn('165465645');

        $this->jpushResponse->expects('get_message_id')
                             ->once()
                             ->with('fghij-67890')
                             ->andReturn('555165655');

        $this->class->dispatch($endpoints, [ 'jpush'  => [ 'notification' => $jpushPayload ]]);

        $property    = $this->getReflectionProperty('dispatchers');
        $dispatchers = $property->getValue($this->class);

        $this->assertArrayHasKey('jpush', $dispatchers);

        $this->assertPropertySame('statuses', $expectedStatuses);
    }

    /**
     * Test dispatch send correct broadcast payload.
     *
     * @covers Lunr\Vortex\PushNotificationDispatcher::dispatch
     */
    public function testDispatchSendsCorrectBroadcastPayload(): void
    {
        $dispatchers = [
            'fcm'   => $this->fcm,
            'email' => $this->email,
        ];
        $this->setReflectionPropertyValue('dispatchers', $dispatchers);

        $dataPayload = $this->getMockBuilder(FCMPayload::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $apnsPayload = $this->getMockBuilder(APNSPayload::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $emailPayload = $this->getMockBuilder(EmailPayload::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $payloads = [
            'apns'  => [ 'apns' => $apnsPayload ],
            'fcm'  => [ 'data' => $dataPayload ],
            'email' => [ 'email' => $emailPayload ],
        ];

        $apnsPayload->expects($this->exactly(2))
                    ->method('is_broadcast')
                    ->willReturn(TRUE);

        $emailPayload->expects($this->exactly(3))
                     ->method('is_broadcast')
                     ->willReturn(TRUE);

        $dataPayload->expects($this->exactly(3))
                    ->method('is_broadcast')
                    ->willReturn(TRUE);

        $this->fcm->expects($this->once())
                  ->method('push')
                  ->with($dataPayload, [])
                  ->willReturn($this->fcmResponse);

        $this->fcmResponse->expects($this->never())
                          ->method('get_status');

        $this->fcmResponse->expects($this->once())
                          ->method('get_broadcast_status')
                          ->willReturn(PushNotificationStatus::Success);

        $this->class->dispatch([], $payloads);

        $expectedStatuses = [
            PushNotificationStatus::Unknown->value         => [ 'email' => [ 'email' => $emailPayload ]],
            PushNotificationStatus::Success->value         => [ 'fcm' => [ 'data' => $dataPayload ] ],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [ 'apns' => [ 'apns' => $apnsPayload ]],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $this->assertPropertySame('broadcastStatuses', $expectedStatuses);
    }

}

?>
