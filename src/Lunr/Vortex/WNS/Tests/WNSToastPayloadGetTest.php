<?php

/**
 * This file contains the WNSToastPayloadGetTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\WNS\Tests;

/**
 * This class contains tests for the getters of the WNSToastPayload class.
 *
 * @covers Lunr\Vortex\WNS\WNSToastPayload
 */
class WNSToastPayloadGetTest extends WNSToastPayloadTestCase
{

    /**
     * Test get_payload() with title being present.
     *
     * @covers Lunr\Vortex\WNS\WNSToastPayload::get_payload
     */
    public function testGetPayloadWithTitle(): void
    {
        $file     = TEST_STATICS . '/Vortex/wns/toast_title.xml';
        $elements = [ 'text' => [ 'Title' ] ];

        $this->setReflectionPropertyValue('elements', $elements);

        $this->assertStringMatchesFormatFile($file, $this->class->get_payload());
    }

    /**
     * Test get_payload() with message being present.
     *
     * @covers Lunr\Vortex\WNS\WNSToastPayload::get_payload
     */
    public function testGetPayloadWithMessage(): void
    {
        $file     = TEST_STATICS . '/Vortex/wns/toast_message.xml';
        $elements = [ 'text' => [ 'Message' ] ];

        $this->setReflectionPropertyValue('elements', $elements);

        $this->assertStringMatchesFormatFile($file, $this->class->get_payload());
    }

    /**
     * Test get_payload() with deeplink being present.
     *
     * @covers Lunr\Vortex\WNS\WNSToastPayload::get_payload
     */
    public function testGetPayloadWithDeeplinkAndTemplate(): void
    {
        $file     = TEST_STATICS . '/Vortex/wns/toast_deeplink.xml';
        $elements = [ 'text' => [], 'template' => 'ToastText01', 'launch' => 'Deeplink' ];

        $this->setReflectionPropertyValue('elements', $elements);

        $this->assertStringMatchesFormatFile($file, $this->class->get_payload());
    }

    /**
     * Test get_payload() with everything being present.
     *
     * @covers Lunr\Vortex\WNS\WNSToastPayload::get_payload
     */
    public function testGetPayload(): void
    {
        $file     = TEST_STATICS . '/Vortex/wns/toast.xml';
        $elements = [ 'text' => [ 'Title', 'Message', 'Hello' ], 'template' => 'ToastText04', 'image' => 'image', 'launch' => 'Deeplink' ];

        $this->setReflectionPropertyValue('elements', $elements);

        $this->assertStringMatchesFormatFile($file, $this->class->get_payload());
    }

}

?>
