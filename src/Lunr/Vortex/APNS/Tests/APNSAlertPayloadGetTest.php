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
class APNSAlertPayloadGetTest extends APNSAlertPayloadTest
{

    /**
     * Test get_payload() with alert being present.
     *
     * @param string $file       The path to the payload file
     * @param array  $data_array The data to compare get_payload against
     *
     * @dataProvider payloadProvider
     * @covers       Lunr\Vortex\APNS\APNSAlertPayload::get_payload
     */
    public function testGetPayloadWithAlert(string $file, array $data_array): void
    {
        $file     = TEST_STATICS . $file;
        $elements = $data_array;

        $this->set_reflection_property_value('elements', $elements);

        $result = $this->get_reflection_method('get_payload')->invokeArgs($this->class, []);

        $this->assertJsonStringEqualsJsonFile($file, json_encode($result));
    }

}

?>
