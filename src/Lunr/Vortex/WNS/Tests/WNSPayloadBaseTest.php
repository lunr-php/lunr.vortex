<?php

/**
 * This file contains the WNSPayloadTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\WNS\Tests;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the WNSPayload class.
 *
 * @covers Lunr\Vortex\WNS\WNSPayload
 */
class WNSPayloadBaseTest extends WNSPayloadTest
{

    /**
     * Test escape_string() works correctly.
     *
     * @param string $string   Unescaped base string
     * @param string $expected Expected escaped string
     *
     * @dataProvider stringProvider
     * @covers       Lunr\Vortex\WNS\WNSPayload::escape_string
     */
    public function testEscapeString($string, $expected): void
    {
        $method = $this->get_accessible_reflection_method('escape_string');

        $this->assertEquals($expected, $method->invokeArgs($this->class, [ $string ]));
    }

    /**
     * Test is_broadcast returns false.
     *
     * @covers Lunr\Vortex\WNS\WNSBadgePayload::is_broadcast
     */
    public function testIsBroadCastReturnFalse(): void
    {
        $this->assertFalse($this->class->is_broadcast());
    }

}

?>
