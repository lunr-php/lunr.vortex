<?php

/**
 * This file contains the APNSLiveActivityPayloadTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS\Tests;

use Lunr\Halo\LunrBaseTest;
use Lunr\Vortex\APNS\APNSLiveActivityPayload;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the APNSLiveActivityPayload class.
 *
 * @covers \Lunr\Vortex\APNS\APNSLiveActivityPayload
 */
abstract class APNSLiveActivityPayloadTest extends LunrBaseTest
{

    /**
     * Sample payload json
     * @var string
     */
    protected string $payload;

    /**
     * Instance of the tested class.
     * @var APNSLiveActivityPayload
     */
    protected APNSLiveActivityPayload $class;

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

        $this->class = new APNSLiveActivityPayload();

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
     * @return array<string, mixed> $values Array of non-object values
     */
    public function payloadProvider(): array
    {
        $values = [];

        $values['alert']       = [ '/Vortex/apns/alert.json', [ 'alert' => 'apnsmessage' ] ];
        $values['custom data'] = [ '/Vortex/apns/custom_data.json', [ 'custom_data' => [ 'key1' => 'value1', 'key2' => 'value2' ] ] ];
        $values['badge']       = [ '/Vortex/apns/badge.json', [ 'badge' => 10 ] ];
        $values['full']        = [
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
