<?php

/**
 * This file contains the APNSAlertPayloadGetTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS\Tests;

/**
 * This class contains tests for the getters of the APNSAlertPayload class.
 *
 * @covers Lunr\Vortex\APNS\APNSAlertPayload
 */
class APNSAlertPayloadGetTest extends APNSAlertPayloadTestCase
{

    /**
     * Test get_payload() with alert being present.
     *
     * @param string $file      The path to the payload file
     * @param array  $dataArray The data to compare get_payload against
     *
     * @dataProvider payloadProvider
     * @covers       Lunr\Vortex\APNS\APNSAlertPayload::get_payload
     */
    public function testGetPayloadWithAlert(string $file, array $dataArray): void
    {
        $file     = TEST_STATICS . $file;
        $elements = $dataArray;

        $this->setReflectionPropertyValue('elements', $elements);

        $result = $this->getReflectionMethod('get_payload')->invokeArgs($this->class, []);

        $this->assertJsonStringEqualsJsonFile($file, json_encode($result));
    }

}

?>
