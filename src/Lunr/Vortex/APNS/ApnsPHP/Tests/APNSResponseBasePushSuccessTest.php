<?php

/**
 * This file contains the APNSResponseBasePushSuccessTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2016 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS\ApnsPHP\Tests;

use Lunr\Vortex\APNS\ApnsPHP\APNSResponse;
use Lunr\Vortex\PushNotificationStatus;

/**
 * This class contains tests for the constructor of the APNSResponse class
 * in case of a push notification success.
 *
 * @covers Lunr\Vortex\APNS\ApnsPHP\APNSResponse
 */
class APNSResponseBasePushSuccessTest extends APNSResponseTestCase
{

    /**
     * Test constructor behavior for push success with single endpoint success.
     *
     * @covers Lunr\Vortex\APNS\ApnsPHP\APNSResponse::__construct
     */
    public function testPushSuccessWithSingleSuccess(): void
    {
        $endpoints        = [ 'endpoint1' ];
        $invalidEndpoints = [];
        $errors           = [];
        $statuses         = [ 'endpoint1' => PushNotificationStatus::Success ];

        $this->class = new APNSResponse($this->logger, $endpoints, $invalidEndpoints, $errors, '{}');

        parent::baseSetUp($this->class);

        $this->assertPropertyEquals('statuses', $statuses);
    }

    /**
     * Test constructor behavior for success of push notification with single error.
     *
     * @covers Lunr\Vortex\APNS\ApnsPHP\APNSResponse::__construct
     */
    public function testPushSuccessWithSingleError(): void
    {
        $endpoints        = [ 'endpoint1' ];
        $invalidEndpoints = [];
        $errors           = [
            1 => [
                'MESSAGE'             => $this->apnsMessage,
                'ERRORS'              => [
                    [
                        'command'       => 8,
                        'statusCode'    => 400,
                        'identifier'    => 100,
                        'time'          => 1465997381,
                        'statusMessage' => '{"reason": "IdleTimeout"}',
                    ],
                ],
            ],
        ];
        $statuses         = [ 'endpoint1' => PushNotificationStatus::TemporaryError ];

        $this->apnsMessage->expects($this->once())
                          ->method('getRecipient')
                          ->willReturn('endpoint1');

        $this->logger->expects('warning')
                     ->once()
                     ->with(
                        'Dispatching APNS notification failed for endpoint {endpoint}: {error}',
                        [ 'endpoint' => 'endpoint1', 'error' => 'IdleTimeout' ]
                     );

        $this->class = new APNSResponse($this->logger, $endpoints, $invalidEndpoints, $errors, '{}');

        parent::baseSetUp($this->class);

        $this->assertPropertyEquals('statuses', $statuses);
    }

    /**
     * Test constructor behavior for success of push notification with multiple success.
     *
     * @covers Lunr\Vortex\APNS\ApnsPHP\APNSResponse::__construct
     */
    public function testPushSuccessWithMultipleSuccess(): void
    {
        $endpoints        = [ 'endpoint1', 'endpoint2', 'endpoint3' ];
        $invalidEndpoints = [];
        $errors           = [];
        $statuses         = [
            'endpoint1' => PushNotificationStatus::Success,
            'endpoint2' => PushNotificationStatus::Success,
            'endpoint3' => PushNotificationStatus::Success,
        ];

        $this->class = new APNSResponse($this->logger, $endpoints, $invalidEndpoints, $errors, '{}');

        parent::baseSetUp($this->class);

        $this->assertPropertyEquals('statuses', $statuses);
    }

    /**
     * Test constructor behavior for success of push notification with multiple errors.
     *
     * @covers Lunr\Vortex\APNS\ApnsPHP\APNSResponse::__construct
     */
    public function testPushSuccessWithMultipleErrors(): void
    {
        $newMessage = function () {
            return $this->getMockBuilder('ApnsPHP\Message')
                        ->disableOriginalConstructor()
                        ->getMock();
        };

        $message1 = $newMessage();
        $message2 = $newMessage();
        $message3 = $newMessage();
        $message4 = $newMessage();
        $message5 = $newMessage();
        $message6 = $newMessage();
        $message7 = $newMessage();

        $endpoints        = [ 'endpoint1', 'endpoint2', 'endpoint3', 'endpoint4', 'endpoint5', 'endpoint6', 'endpoint7' ];
        $invalidEndpoints = [];
        $errors           = [
            1 => [
                'MESSAGE'             => $message1,
                'ERRORS'              => [
                    [
                        'command'       => 8,
                        'statusCode'    => 501,
                        'identifier'    => 100,
                        'time'          => 1465997381,
                        'statusMessage' => '{"reason": "TopicDisallowed"}',
                    ],
                ],
            ],
            2 => [
                'MESSAGE'             => $message2,
                'ERRORS'              => [
                    [
                        'command'       => 5,
                        'statusCode'    => 501,
                        'identifier'    => 2,
                        'time'          => 1465997382,
                        'statusMessage' => '{"reason": "BadCertificate"}',
                    ],
                ],
            ],
            3 => [
                'MESSAGE'             => $message3,
                'ERRORS'              => [
                    [
                        'command'       => 1,
                        'statusCode'    => 501,
                        'identifier'    => 3,
                        'time'          => 1465997383,
                        'statusMessage' => '{"reason": "BadCertificateEnvironment"}',
                    ],
                ],
            ],
            4 => [
                'MESSAGE'             => $message4,
                'ERRORS'              => [
                    [
                        'command'       => 10,
                        'statusCode'    => 501,
                        'identifier'    => 4,
                        'time'          => 1465997390,
                        'statusMessage' => '{"reason": "InvalidProviderToken"}',
                    ],
                ],
            ],
            5 => [
                'MESSAGE'             => $message5,
                'ERRORS'              => [
                    [
                        'command'       => 10,
                        'statusCode'    => 501,
                        'identifier'    => 5,
                        'time'          => 1465997390,
                        'statusMessage' => '{"reason": "ExpiredProviderToken"}',
                    ],
                ],
            ],
            6 => [
                'MESSAGE'             => $message6,
                'ERRORS'              => [
                    [
                        'command'       => 10,
                        'statusCode'    => 501,
                        'identifier'    => 6,
                        'time'          => 1465997390,
                        'statusMessage' => '{"reason": "BadDeviceToken"}',
                    ],
                ],
            ],
            7 => [
                'MESSAGE'             => $message7,
                'ERRORS'              => [
                    [
                        'command'       => 10,
                        'statusCode'    => 501,
                        'identifier'    => 7,
                        'time'          => 1465997390,
                        'statusMessage' => '{"reason": "DeviceTokenNotForTopic"}',
                    ],
                ],
            ],
        ];
        $statuses         = [
            'endpoint1' => PushNotificationStatus::Error,
            'endpoint2' => PushNotificationStatus::Error,
            'endpoint3' => PushNotificationStatus::Error,
            'endpoint4' => PushNotificationStatus::Error,
            'endpoint5' => PushNotificationStatus::TemporaryError,
            'endpoint6' => PushNotificationStatus::InvalidEndpoint,
            'endpoint6' => PushNotificationStatus::InvalidEndpoint,
            'endpoint7' => PushNotificationStatus::InvalidEndpoint,
        ];

        $message1->expects($this->once())
                 ->method('getRecipient')
                 ->willReturn('endpoint1');

        $message2->expects($this->once())
                 ->method('getRecipient')
                 ->willReturn('endpoint2');

        $message3->expects($this->once())
                 ->method('getRecipient')
                 ->willReturn('endpoint3');

        $message4->expects($this->once())
                 ->method('getRecipient')
                 ->willReturn('endpoint4');

        $message5->expects($this->once())
                 ->method('getRecipient')
                 ->willReturn('endpoint5');

        $message6->expects($this->once())
                 ->method('getRecipient')
                 ->willReturn('endpoint6');

        $message7->expects($this->once())
                 ->method('getRecipient')
                 ->willReturn('endpoint7');

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching APNS notification failed for endpoint {endpoint}: {error}',
                        [ 'endpoint' => 'endpoint1', 'error' => 'TopicDisallowed' ]
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching APNS notification failed for endpoint {endpoint}: {error}',
                        [ 'endpoint' => 'endpoint2', 'error' => 'BadCertificate' ],
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching APNS notification failed for endpoint {endpoint}: {error}',
                        [ 'endpoint' => 'endpoint3', 'error' => 'BadCertificateEnvironment' ],
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching APNS notification failed for endpoint {endpoint}: {error}',
                        [ 'endpoint' => 'endpoint4', 'error' => 'InvalidProviderToken' ],
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching APNS notification failed for endpoint {endpoint}: {error}',
                        [ 'endpoint' => 'endpoint5', 'error' => 'ExpiredProviderToken' ],
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching APNS notification failed for endpoint {endpoint}: {error}',
                        [ 'endpoint' => 'endpoint6', 'error' => 'BadDeviceToken' ],
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching APNS notification failed for endpoint {endpoint}: {error}',
                        [ 'endpoint' => 'endpoint7', 'error' => 'DeviceTokenNotForTopic' ]
                     );

        $this->class = new APNSResponse($this->logger, $endpoints, $invalidEndpoints, $errors, '{}');

        parent::baseSetUp($this->class);

        $this->assertPropertyEquals('statuses', $statuses);
    }

    /**
     * Test constructor behavior for success of push notification with multiple mixed results and invalid endpoints
     *
     * @covers Lunr\Vortex\APNS\ApnsPHP\APNSResponse::__construct
     */
    public function testPushSuccessWithMultipleMixedResultsAndInvalidEndpoints(): void
    {
        $newMessage = function () {
            return $this->getMockBuilder('ApnsPHP\Message')
                        ->disableOriginalConstructor()
                        ->getMock();
        };

        $message = $newMessage();

        $endpoints        = [ 'endpoint1', 'endpoint2', 'endpoint3', 'endpoint4', 'endpoint5' ];
        $invalidEndpoints = [ 'endpoint2', 'endpoint3' ];
        $errors           = [
            4 => [
                'MESSAGE'             => $message,
                'ERRORS'              => [
                    [
                        'command'       => 1,
                        'statusCode'    => 410,
                        'identifier'    => 4,
                        'time'          => 1465997390,
                        'statusMessage' => '{"reason": "ExpiredProviderToken"}',
                    ],
                ],
            ],
        ];
        $statuses         = [
            'endpoint1' => PushNotificationStatus::Success,
            'endpoint2' => PushNotificationStatus::InvalidEndpoint,
            'endpoint3' => PushNotificationStatus::InvalidEndpoint,
            'endpoint4' => PushNotificationStatus::TemporaryError,
            'endpoint5' => PushNotificationStatus::Success,
        ];

        $message->expects($this->once())
                ->method('getRecipient')
                ->willReturn('endpoint4');

        $this->logger->expects('warning')
                     ->once()
                     ->with(
                        'Dispatching APNS notification failed for endpoint {endpoint}: {error}',
                        [ 'endpoint' => 'endpoint4', 'error' => 'ExpiredProviderToken' ]
                     );

        $this->class = new APNSResponse($this->logger, $endpoints, $invalidEndpoints, $errors, '{}');

        parent::baseSetUp($this->class);

        $this->assertPropertyEquals('statuses', $statuses);
    }

}

?>
