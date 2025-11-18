<?php

/**
 * This file contains the EmailDispatcherBaseTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2014 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\Email\Tests;

use Lunr\Halo\PropertyTraits\PsrLoggerTestTrait;

/**
 * This class contains test for the constructor of the EmailDispatcher class.
 *
 * @covers Lunr\Vortex\Email\EmailDispatcher
 */
class EmailDispatcherBaseTest extends EmailDispatcherTestCase
{

    use PsrLoggerTestTrait;

    /**
     * Test that the source is set to an empty string by default.
     */
    public function testSourceIsEmptyString(): void
    {
        $this->assertPropertyEmpty('source');
    }

    /**
     * Test that the passed Mail object is set correctly.
     */
    public function testMailIsSetCorrectly(): void
    {
        $this->assertSame($this->mailTransport, $this->getReflectionPropertyValue('mailTransport'));
    }

    /**
     * Test that clone_mail() returns a cloned email transport class.
     *
     * @covers Lunr\Vortex\Email\EmailDispatcher::clone_mail
     */
    public function testCloneMailReturnsClonedMailClass(): void
    {
        $method = $this->getReflectionMethod('clone_mail');

        $mail = $method->invoke($this->class);

        $this->assertNotSame($mail, $this->mailTransport);
    }

}

?>
