<?php

/**
 * This file contains the WNSToastPayloadSetTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\WNS\Tests;

/**
 * This class contains tests for the setters of the WNSToastPayload class.
 *
 * @covers Lunr\Vortex\WNS\WNSToastPayload
 */
class WNSToastPayloadSetTest extends WNSToastPayloadTestCase
{

    /**
     * Test set_text() works correctly for strings.
     *
     * @covers Lunr\Vortex\WNS\WNSToastPayload::set_text
     */
    public function testSetTextFirstLine(): void
    {
        $this->class->set_text('&title');

        $value = $this->getReflectionPropertyValue('elements');

        $this->assertArrayHasKey('text', $value);
        $this->assertEquals('&amp;title', $value['text'][0]);
    }

    /**
     * Test set_text() works correctly for arrays.
     *
     * @covers Lunr\Vortex\WNS\WNSToastPayload::set_text
     */
    public function testSetTextArray(): void
    {
        $this->class->set_text([ 'title', 'message' ]);

        $value = $this->getReflectionPropertyValue('elements');

        $this->assertArrayHasKey('text', $value);
        $this->assertEquals('title', $value['text'][0]);
        $this->assertEquals('message', $value['text'][1]);
    }

    /**
     * Test fluid interface of set_text().
     *
     * @covers Lunr\Vortex\WNS\WNSToastPayload::set_text
     */
    public function testSetTextReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_text('title'));
    }

    /**
     * Test set_text() works correctly for custom lines.
     *
     * @covers Lunr\Vortex\WNS\WNSToastPayload::set_text
     */
    public function testSetTextMessage(): void
    {
        $this->class->set_text('&message', 1);

        $value = $this->getReflectionPropertyValue('elements');

        $this->assertArrayHasKey('text', $value);
        $this->assertEquals('&amp;message', $value['text'][1]);
    }

    /**
     * Test set_launch() with correct links.
     *
     * @covers Lunr\Vortex\WNS\WNSToastPayload::set_launch
     */
    public function testSetLaunchWithCorrectLink(): void
    {
        $this->class->set_launch('/page&link');

        $value = $this->getReflectionPropertyValue('elements');

        $this->assertArrayHasKey('launch', $value);
        $this->assertEquals('/page&amp;link', $value['launch']);
    }

    /**
     * Test fluid interface of set_launch().
     *
     * @covers Lunr\Vortex\WNS\WNSToastPayload::set_launch
     */
    public function testSetLaunchReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_launch('link'));
    }

    /**
     * Test set_template() with correct links.
     *
     * @covers Lunr\Vortex\WNS\WNSToastPayload::set_template
     */
    public function testSetTemplateWithCorrectLink(): void
    {
        $this->class->set_template('ToastText04');

        $value = $this->getReflectionPropertyValue('elements');

        $this->assertArrayHasKey('template', $value);
        $this->assertEquals('ToastText04', $value['template']);
    }

    /**
     * Test fluid interface of set_template().
     *
     * @covers Lunr\Vortex\WNS\WNSToastPayload::set_template
     */
    public function testSetTemplateReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_template('link'));
    }

    /**
     * Test set_image() with correct value.
     *
     * @covers Lunr\Vortex\WNS\WNSToastPayload::set_image
     */
    public function testSetImage(): void
    {
        $this->class->set_image('https://image.url/img.jpg');

        $value = $this->getReflectionPropertyValue('elements');

        $this->assertArrayHasKey('image', $value);
        $this->assertEquals('https://image.url/img.jpg', $value['image']);
    }

    /**
     * Test fluid interface of set_image().
     *
     * @covers Lunr\Vortex\WNS\WNSToastPayload::set_image
     */
    public function testSetImageReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_image('image'));
    }

}

?>
