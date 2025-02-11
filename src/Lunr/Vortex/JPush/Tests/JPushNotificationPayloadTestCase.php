<?php

/**
 * This file contains the JPushNotificationPayloadTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2020 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush\Tests;

use Lunr\Halo\LunrBaseTestCase;
use Lunr\Vortex\JPush\JPushNotificationPayload;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the JPushNotificationPayload class.
 *
 * @covers Lunr\Vortex\JPush\JPushNotificationPayload
 */
abstract class JPushNotificationPayloadTestCase extends LunrBaseTestCase
{

    /**
     * Sample payload json
     * @var string
     */
    protected $payload;

    /**
     * Instance of the tested class.
     * @var JPushNotificationPayload
     */
    protected JPushNotificationPayload $class;

    /**
     * Testcase Constructor.
     */
    public function setUp(): void
    {
        $elements_array = [
            'registration_ids' => [ 'one', 'two', 'three' ],
            'collapse_key'     => 'test',
            'data'             => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
            'time_to_live'     => 10,
        ];

        $this->payload = json_encode($elements_array);

        $this->class = new JPushNotificationPayload();

        parent::baseSetUp($this->class);
    }

    /**
     * Testcase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->payload);
        unset($this->class);

        parent::tearDown();
    }

}

?>
