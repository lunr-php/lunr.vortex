<?php

/**
 * This file contains the FCMPayloadTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2017 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use Lunr\Halo\LunrBaseTestCase;
use Lunr\Vortex\FCM\FCMAndroidPayload;
use Lunr\Vortex\FCM\FCMApnsPayload;
use Lunr\Vortex\FCM\FCMPayload;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the FCMPayload class.
 *
 * @covers Lunr\Vortex\FCM\FCMPayload
 */
abstract class FCMPayloadTestCase extends LunrBaseTestCase
{

    /**
     * Sample payload json
     * @var string
     */
    protected $payload;

    /**
     * Sample android payload
     * @var FCMAndroidPayload
     */
    protected $androidPayload;

    /**
     * Sample apns payload
     * @var FCMApnsPayload
     */
    protected $apnsPayload;

    /**
     * Instance of the tested class.
     * @var FCMPayload&MockObject&Stub
     */
    protected FCMPayload&MockObject&Stub $class;

    /**
     * Testcase Constructor.
     */
    public function setUp(): void
    {
        $elementsArray = [
            'registration_ids' => [ 'one', 'two', 'three' ],
            'collapse_key'     => 'test',
            'data'             => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
            'time_to_live'     => 10,
        ];

        $this->payload = json_encode($elementsArray);

        $this->androidPayload = $this->getMockBuilder(FCMAndroidPayload::class)
                                     ->getMock();

        $this->apnsPayload = $this->getMockBuilder(FCMApnsPayload::class)
                                  ->getMock();

        $this->class = $this->getMockBuilder('Lunr\Vortex\FCM\FCMPayload')
                            ->getMockForAbstractClass();

        parent::baseSetUp($this->class);
    }

    /**
     * Testcase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->payload);
        unset($this->androidPayload);
        unset($this->apnsPayload);
        unset($this->class);

        parent::tearDown();
    }

}

?>
