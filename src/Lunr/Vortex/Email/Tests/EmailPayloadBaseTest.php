<?php

/**
 * This file contains the EmailPayloadBaseTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2014 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\Email\Tests;

/**
 * This class contains the Base tests of the EmailPayload class.
 *
 * @covers Lunr\Vortex\Email\EmailPayload
 */
class EmailPayloadBaseTest extends EmailPayloadTest
{

    /**
     * Test elements is initialized as an empty array.
     */
    public function testElementsIsInitializedAsEmptyArray(): void
    {
        $expected = [
            'charset'      => 'UTF-8',
            'encoding'     => 'base64',
            'subject'      => '',
            'body'         => '',
            'body_as_html' => FALSE,
        ];

        $this->assertEquals($expected, $this->get_reflection_property_value('elements'));
    }

    /**
     * Test is_broadcast returns false.
     *
     * @covers Lunr\Vortex\Email\EmailPayload::is_broadcast
     */
    public function testIsBroadCastReturnFalse(): void
    {
        $this->assertFalse($this->class->is_broadcast());
    }

}

?>
