<?php

/**
 * This file contains the APNSAlertPayloadTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS\Tests;

use Lunr\Halo\LunrBaseTest;
use Lunr\Vortex\APNS\APNSAlertPayload;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the APNSAlertPayload class.
 *
 * @covers Lunr\Vortex\APNS\APNSAlertPayload
 */
abstract class APNSAlertPayloadTest extends LunrBaseTest
{

    /**
     * Sample payload json
     * @var string
     */
    protected string $payload;

    /**
     * Instance of the tested class.
     * @var APNSAlertPayload
     */
    protected APNSAlertPayload $class;

    /**
     * Testcase Constructor.
     */
    public function setUp(): void
    {
        $elements_array = [
            'alert'       => 'apnsmessage',
            'badge'       => 10,
            'sound'       => 'bingbong.wav',
            'custom_data' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
        ];

        $this->payload = json_encode($elements_array);

        $this->class = new APNSAlertPayload();

        parent::baseSetUp($this->class);
    }

    /**
     * TestCase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->payload);
        unset($this->class);

        parent::tearDown();
    }

    /**
     * Unit test data provider for payload files.
     *
     * @return array $values Array of non-object values
     */
    public function payloadProvider(): array
    {
        $values   = [];
        $values[] = [ '/Vortex/apns/alert.json', [ 'alert' => 'apnsmessage' ] ];
        $values[] = [ '/Vortex/apns/custom_data.json', [ 'custom_data' => [ 'key1' => 'value1', 'key2' => 'value2' ] ] ];
        $values[] = [ '/Vortex/apns/badge.json', [ 'badge' => 10 ] ];
        $values[] = [
            '/Vortex/apns/apns.json',
            [
                'alert'       => 'apnsmessage',
                'badge'       => 10,
                'sound'       => 'bingbong.wav',
                'custom_data' => [ 'key1' => 'value1', 'key2' => 'value2' ],
            ],
        ];

        return $values;
    }

}

?>
