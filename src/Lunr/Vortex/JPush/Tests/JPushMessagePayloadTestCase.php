<?php

/**
 * This file contains the JPushMessagePayloadTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2020 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush\Tests;

use Lunr\Halo\LunrBaseTestCase;
use Lunr\Vortex\JPush\JPushMessagePayload;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the JPushMessagePayload class.
 *
 * @covers Lunr\Vortex\JPush\JPushMessagePayload
 */
abstract class JPushMessagePayloadTestCase extends LunrBaseTestCase
{

    /**
     * Sample payload json
     * @var string
     */
    protected $payload;

    /**
     * Instance of the tested class.
     * @var JPushMessagePayload
     */
    protected JPushMessagePayload $class;

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

        $this->class = new JPushMessagePayload();

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
