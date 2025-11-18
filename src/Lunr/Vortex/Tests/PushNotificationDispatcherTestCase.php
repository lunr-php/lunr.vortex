<?php

/**
 * This file contains the PushNotificationDispatcherTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\Tests;

use Lunr\Halo\LunrBaseTestCase;
use Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher;
use Lunr\Vortex\APNS\ApnsPHP\APNSResponse;
use Lunr\Vortex\Email\EmailDispatcher;
use Lunr\Vortex\Email\EmailResponse;
use Lunr\Vortex\FCM\FCMDispatcher;
use Lunr\Vortex\FCM\FCMResponse;
use Lunr\Vortex\JPush\JPushDispatcher;
use Lunr\Vortex\JPush\JPushResponse;
use Lunr\Vortex\PushNotificationDispatcher;
use Lunr\Vortex\PushNotificationStatus;
use Lunr\Vortex\WNS\WNSDispatcher;
use Lunr\Vortex\WNS\WNSResponse;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the PushNotificationDispatcher class.
 *
 * @covers PushNotificationDispatcher
 */
abstract class PushNotificationDispatcherTestCase extends LunrBaseTestCase
{

    use MockeryPHPUnitIntegration;

    /**
     * Mock instance of the APNSDispatcher class
     * @var APNSDispatcher&MockObject&Stub
     */
    protected APNSDispatcher&MockObject&Stub $apns;

    /**
     * Mock instance of the EmailDispatcher class
     * @var EmailDispatcher&MockObject&Stub
     */
    protected EmailDispatcher&MockObject&Stub $email;

    /**
     * Mock instance of the FCMDispatcher class
     * @var FCMDispatcher&MockObject&Stub
     */
    protected FCMDispatcher&MockObject&Stub $fcm;

    /**
     * Mock instance of the WNSDispatcher class
     * @var WNSDispatcher&MockObject&Stub
     */
    protected WNSDispatcher&MockObject&Stub $wns;

    /**
     * Mock instance of the JPushDispatcher class
     * @var JPushDispatcher&MockObject&Stub
     */
    protected JPushDispatcher&MockObject&Stub $jpush;

    /**
     * Mock instance of the APNSResponse class
     * @var APNSResponse&MockInterface
     */
    protected APNSResponse&MockInterface $apnsResponse;

    /**
     * Mock instance of the FCMResponse class
     * @var FCMResponse&MockObject&Stub
     */
    protected FCMResponse&MockObject&Stub $fcmResponse;

    /**
     * Mock instance of the EmailResponse class
     * @var EmailResponse&MockObject&Stub
     */
    protected EmailResponse&MockObject&Stub $emailResponse;

    /**
     * Mock instance of the WNSResponse class
     * @var WNSResponse&MockInterface
     */
    protected WNSResponse&MockInterface $wnsResponse;

    /**
     * Mock instance of the JPushResponse class
     * @var JPushResponse&MockInterface
     */
    protected JPushResponse&MockInterface $jpushResponse;

    /**
     * Instance of the tested class.
     * @var PushNotificationDispatcher
     */
    protected PushNotificationDispatcher $class;

    /**
     * Testcase Constructor.
     */
    public function setUp(): void
    {
        $this->apns = $this->getMockBuilder(APNSDispatcher::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $this->email = $this->getMockBuilder(EmailDispatcher::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $this->fcm = $this->getMockBuilder(FCMDispatcher::class)
                          ->disableOriginalConstructor()
                          ->getMock();

        $this->wns = $this->getMockBuilder(WNSDispatcher::class)
                          ->disableOriginalConstructor()
                          ->getMock();

        $this->jpush = $this->getMockBuilder(JPushDispatcher::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $this->fcmResponse = $this->getMockBuilder(FCMResponse::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();

        $this->emailResponse = $this->getMockBuilder(EmailResponse::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->apnsResponse  = Mockery::mock(APNSResponse::class);
        $this->wnsResponse   = Mockery::mock(WNSResponse::class);
        $this->jpushResponse = Mockery::mock(JPushResponse::class);

        $this->class = new PushNotificationDispatcher();

        $this->baseSetUp($this->class);
    }

    /**
     * Testcase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->apns);
        unset($this->email);
        unset($this->fcm);
        unset($this->wns);
        unset($this->jpush);
        unset($this->apnsResponse);
        unset($this->emailResponse);
        unset($this->fcmResponse);
        unset($this->wnsResponse);
        unset($this->jpushResponse);
        unset($this->class);

        parent::tearDown();
    }

    /**
     * Unit test data provider for push notification status codes.
     *
     * @return array Array of status codes and expected status keys
     */
    public function statusCodesProvider(): array
    {
        $values   = [];
        $values[] = [ PushNotificationStatus::Unknown ];
        $values[] = [ PushNotificationStatus::Success ];
        $values[] = [ PushNotificationStatus::Error ];
        $values[] = [ PushNotificationStatus::InvalidEndpoint ];
        $values[] = [ PushNotificationStatus::TemporaryError ];
        $values[] = [ PushNotificationStatus::ClientError ];

        return $values;
    }

}

?>
