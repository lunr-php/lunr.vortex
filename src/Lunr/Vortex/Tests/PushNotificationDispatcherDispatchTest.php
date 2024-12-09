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
class PushNotificationDispatcherDispatchTest extends PushNotificationDispatcherTest
{

    /**
     * Test that dispatch doesn't do any push in case of no endpoint.
     *
     * @covers Lunr\Vortex\PushNotificationDispatcher::dispatch
     */
    public function testDispatchDoesNoPushIfNoEndpoint(): void
    {
        $apns_payload = $this->getMockBuilder(APNSPayload::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $data_payload = $this->getMockBuilder(FCMPayload::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $endpoints = [];
        $payloads  = [
            'apns' => [ 'apns' => $apns_payload ],
            'fcm'  => [ 'data' => $data_payload ],
        ];

        $this->apns->expects($this->never())
                   ->method('push');

        $this->fcm->expects($this->never())
                  ->method('push');

        $this->class->dispatch($endpoints, $payloads);

        $empty_statuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $this->assertPropertySame('statuses', $empty_statuses);
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

        $payloads          = [];
        $expected_statuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $expected_statuses[PushNotificationStatus::NotHandled->value] = [
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

        $this->assertPropertySame('statuses', $expected_statuses);
    }

    /**
     * Test that dispatch doesn't do any push in case of no endpoint for the payloads defined.
     *
     * @covers Lunr\Vortex\PushNotificationDispatcher::dispatch
     */
    public function testDispatchDoesNoPushIfNoEndpointsForPayloads(): void
    {
        $wns_payload = $this->getMockBuilder(WNSTilePayload::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $email_payload = $this->getMockBuilder(EmailPayload::class)
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
            'wns'   => [ 'tile'  => $wns_payload ],
            'email' => [ 'email' => $email_payload ],
        ];

        $expected_statuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $expected_statuses[PushNotificationStatus::NotHandled->value] = [
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

        $this->assertPropertySame('statuses', $expected_statuses);
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

        $this->set_reflection_property_value('dispatchers', $dispatchers);

        $apns_payload = $this->getMockBuilder(APNSPayload::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $fcm_payload = $this->getMockBuilder(FCMPayload::class)
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
            'apns' => [ 'notification' => $apns_payload ],
            'fcm'  => [ 'notification' => $fcm_payload ],
        ];

        $expected_statuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $expected_statuses[PushNotificationStatus::NotHandled->value] = [
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

        $this->assertPropertySame('statuses', $expected_statuses);
    }

    /**
     * Test that dispatch doesn't do any push in case of no endpoint for the payloads defined.
     *
     * @covers Lunr\Vortex\PushNotificationDispatcher::dispatch
     */
    public function testDispatchDoesNoPushIfNoEndpointsForPayloadTypeAndNoDispatcher(): void
    {
        $apns_payload = $this->getMockBuilder(APNSPayload::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $fcm_payload = $this->getMockBuilder(FCMPayload::class)
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
            'apns' => [ 'notification' => $apns_payload ],
            'fcm'  => [ 'notification' => $fcm_payload ],
        ];

        $expected_statuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $expected_statuses[PushNotificationStatus::NotHandled->value] = [
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

        $this->assertPropertySame('statuses', $expected_statuses);
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
        $this->set_reflection_property_value('dispatchers', $dispatchers);

        $apns_payload = $this->getMockBuilder(APNSPayload::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $data_payload = $this->getMockBuilder(FCMPayload::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $email_payload = $this->getMockBuilder(EmailPayload::class)
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
            'apns'  => [ 'apns' => $apns_payload ],
            'email' => [ 'email' => $email_payload ],
            'fcm'   => [ 'data' => $data_payload ],
        ];

        $expected_statuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $expected_statuses[PushNotificationStatus::Success->value] = [
            [
                'endpoint'    => 'fghij-67890',
                'platform'    => 'fcm',
                'payloadType' => 'data',
                'client'      => 'Android',
                'language'    => 'en-US',
            ],
        ];

        $expected_statuses[PushNotificationStatus::NotHandled->value] = [
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
                   ->with($data_payload, [ 'fghij-67890' ])
                   ->willReturn($this->fcm_response);

        $this->fcm_response->expects($this->once())
                          ->method('get_status')
                          ->with('fghij-67890')
                          ->willReturn(PushNotificationStatus::Success);

        $this->apns->expects($this->never())
                   ->method('push');

        $this->class->dispatch($endpoints, $payloads);

        $this->assertPropertySame('statuses', $expected_statuses);
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
        $this->set_reflection_property_value('dispatchers', $dispatchers);

        $apns_payload = $this->getMockBuilder(APNSPayload::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $data_payload = $this->getMockBuilder(FCMPayload::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $email_payload = $this->getMockBuilder(EmailPayload::class)
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
            'apns'  => [ 'apns' => $apns_payload ],
            'email' => [ 'email' => $email_payload ],
            'fcm'   => [ 'data' => $data_payload ],
        ];

        $this->fcm->expects($this->once())
                   ->method('push')
                   ->with($data_payload, [ 'fghij-67890' ])
                   ->willReturn($this->fcm_response);

        $this->apns->expects($this->once())
                   ->method('push')
                   ->with($apns_payload, [ 'abcde-12345' ])
                   ->willReturn($this->apns_response);

        $this->fcm_response->expects($this->once())
                           ->method('get_status')
                           ->willReturn(PushNotificationStatus::Success);

        $this->apns_response->expects($this->once())
                            ->method('get_status')
                            ->willReturn(PushNotificationStatus::Success);

        $this->class->dispatch($endpoints, $payloads);

        $property    = $this->get_accessible_reflection_property('dispatchers');
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
        $this->set_reflection_property_value('dispatchers', [ 'wns' => $this->wns ]);

        $tile_payload = $this->getMockBuilder(WNSTilePayload::class)
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
            'wns' => [ 'tile' => $tile_payload ],
        ];

        $expected_statuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $expected_statuses[PushNotificationStatus::Success->value] = [
            [
                'endpoint'    => 'abcde-12345',
                'platform'    => 'wns',
                'payloadType' => 'tile',
                'client'      => 'Blackberry',
                'language'    => 'en-US',
            ],
        ];

        $expected_statuses[PushNotificationStatus::Error->value] = [
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
                  ->willReturn($this->wns_response);

        $this->wns_response->expects($this->exactly(2))
                           ->method('get_status')
                           ->withConsecutive([ 'abcde-12345' ], [ 'abcde-56789' ])
                           ->will($this->onConsecutiveCalls(
                            PushNotificationStatus::Success,
                            PushNotificationStatus::Error
                           ));

        $this->class->dispatch($endpoints, $payloads);

        $this->assertPropertySame('statuses', $expected_statuses);
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
        $this->set_reflection_property_value('dispatchers', $dispatchers);

        $apns_payload = $this->getMockBuilder(APNSPayload::class)
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
            'apns' => [ 'apns' => $apns_payload ],
        ];

        $expected_statuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $expected_statuses[PushNotificationStatus::Success->value] = [
            [
                'endpoint'    => 'endpoint1',
                'platform'    => 'apns',
                'payloadType' => 'apns',
                'client'      => 'iOS',
                'language'    => 'en-US',
            ],
        ];

        $expected_statuses[PushNotificationStatus::Error->value] = [
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
                   ->with($apns_payload, [ 'endpoint1', 'endpoint2' ])
                   ->willReturn($this->apns_response);

        $this->apns_response->expects($this->exactly(2))
                            ->method('get_status')
                            ->withConsecutive([ 'endpoint1' ], [ 'endpoint2' ])
                            ->will($this->onConsecutiveCalls(
                                PushNotificationStatus::Success,
                                PushNotificationStatus::Error
                            ));
        $this->class->dispatch($endpoints, $payloads);

        $this->assertPropertySame('statuses', $expected_statuses);
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
        $this->set_reflection_property_value('dispatchers', $dispatchers);

        $apns_payload = $this->getMockBuilder(APNSPayload::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $email_payload = $this->getMockBuilder(EmailPayload::class)
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
            'apns'  => [ 'apns' => $apns_payload ],
            'email' => [ 'email' => $email_payload ],
        ];

        $expected_statuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $expected_statuses[PushNotificationStatus::Success->value] = [
            [
                'endpoint'    => 'abcde-12345',
                'platform'    => 'apns',
                'payloadType' => 'apns',
                'client'      => 'iOS',
                'language'    => 'en-US',
            ],
        ];

        $expected_statuses[PushNotificationStatus::NotHandled->value] = [
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
                   ->with($apns_payload, [ 'abcde-12345' ])
                   ->willReturn($this->apns_response);

        $this->apns_response->expects($this->once())
                            ->method('get_status')
                            ->with('abcde-12345')
                            ->willReturn(PushNotificationStatus::Success);

        $this->class->dispatch($endpoints, $payloads);

        $property    = $this->get_accessible_reflection_property('dispatchers');
        $dispatchers = $property->getValue($this->class);

        $this->assertArrayHasKey('apns', $dispatchers);
        $this->assertArrayHasKey('fcm', $dispatchers);

        $this->assertPropertySame('statuses', $expected_statuses);
    }

    /**
     * Test dispatch with multi cast and get deferred response batches
     *
     * @covers Lunr\Vortex\PushNotificationDispatcher::dispatch
     */
    public function testDispatchMultiCastWithDeferredResponse(): void
    {
        $this->set_reflection_property_value('dispatchers', [ 'jpush' => $this->jpush ]);

        $jpush_payload = $this->getMockBuilder(JPushMessagePayload::class)
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

        $expected_statuses = [
            PushNotificationStatus::Unknown->value         => [],
            PushNotificationStatus::Success->value         => [],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $expected_statuses[PushNotificationStatus::Success->value] = [
            [
                'endpoint'    => 'endpoint1',
                'platform'    => 'jpush',
                'payloadType' => 'notification',
                'client'      => 'Android',
                'language'    => 'en-US',
            ],
        ];

        $expected_statuses[PushNotificationStatus::Deferred->value] = [
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
                    ->with($jpush_payload, [ 'abcde-12345', 'fghij-67890', 'endpoint1' ])
                    ->willReturn($this->jpush_response);

        $this->jpush_response->expects($this->exactly(3))
                             ->method('get_status')
                             ->withConsecutive([ 'abcde-12345' ], [ 'fghij-67890' ], [ 'endpoint1' ])
                             ->willReturnOnConsecutiveCalls(
                                 PushNotificationStatus::Deferred,
                                 PushNotificationStatus::Deferred,
                                 PushNotificationStatus::Success,
                             );

        $this->jpush_response->expects($this->exactly(2))
                             ->method('get_message_id')
                             ->withConsecutive([ 'abcde-12345' ], [ 'fghij-67890' ])
                             ->willReturnOnConsecutiveCalls('165465645', '555165655');

        $this->class->dispatch($endpoints, [ 'jpush'  => [ 'notification' => $jpush_payload ]]);

        $property    = $this->get_accessible_reflection_property('dispatchers');
        $dispatchers = $property->getValue($this->class);

        $this->assertArrayHasKey('jpush', $dispatchers);

        $this->assertPropertySame('statuses', $expected_statuses);
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
        $this->set_reflection_property_value('dispatchers', $dispatchers);

        $data_payload = $this->getMockBuilder(FCMPayload::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $apns_payload = $this->getMockBuilder(APNSPayload::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $email_payload = $this->getMockBuilder(EmailPayload::class)
                              ->disableOriginalConstructor()
                              ->getMock();

        $payloads = [
            'apns'  => [ 'apns' => $apns_payload ],
            'fcm'  => [ 'data' => $data_payload ],
            'email' => [ 'email' => $email_payload ],
        ];

        $apns_payload->expects($this->exactly(2))
                     ->method('is_broadcast')
                     ->willReturn(TRUE);

        $email_payload->expects($this->exactly(3))
                     ->method('is_broadcast')
                     ->willReturn(TRUE);

        $data_payload->expects($this->exactly(3))
                     ->method('is_broadcast')
                     ->willReturn(TRUE);

        $this->fcm->expects($this->once())
                   ->method('push')
                   ->with($data_payload, [])
                   ->willReturn($this->fcm_response);

        $this->fcm_response->expects($this->never())
                           ->method('get_status');

        $this->fcm_response->expects($this->once())
                           ->method('get_broadcast_status')
                           ->willReturn(PushNotificationStatus::Success);

        $this->class->dispatch([], $payloads);

        $expected_statuses = [
            PushNotificationStatus::Unknown->value         => [ 'email' => [ 'email' => $email_payload ]],
            PushNotificationStatus::Success->value         => [ 'fcm' => [ 'data' => $data_payload ] ],
            PushNotificationStatus::TemporaryError->value  => [],
            PushNotificationStatus::InvalidEndpoint->value => [],
            PushNotificationStatus::ClientError->value     => [],
            PushNotificationStatus::Error->value           => [],
            PushNotificationStatus::NotHandled->value      => [ 'apns' => [ 'apns' => $apns_payload ]],
            PushNotificationStatus::Deferred->value        => [],
        ];

        $this->assertPropertySame('broadcast_statuses', $expected_statuses);
    }

}

?>
