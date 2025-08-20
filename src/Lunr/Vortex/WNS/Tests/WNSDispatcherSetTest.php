<?php

/**
 * This file contains the WNSDispatcherSetTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\WNS\Tests;

use Lunr\Vortex\WNS\WNSType;

/**
 * This class contains tests for the setters of the WNSDispatcher class.
 *
 * @covers Lunr\Vortex\WNS\WNSDispatcher
 */
class WNSDispatcherSetTest extends WNSDispatcherTestCase
{

    /**
     * Test that set_client_id() sets the client_id.
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::set_client_id
     */
    public function testSetClientIDSetsClientID(): void
    {
        $this->class->set_client_id('client_id');

        $this->assertPropertyEquals('client_id', 'client_id');
    }

    /**
     * Test the fluid interface of set_client_id().
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::set_client_id
     */
    public function testSetClientIDReturnsSelfReference(): void
    {
        $this->assertEquals($this->class, $this->class->set_client_id('client_id'));
    }

    /**
     * Test that set_client_secret() sets the client_secret.
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::set_client_secret
     */
    public function testSetClientSecretSetsClientSecret(): void
    {
        $this->class->set_client_id('client_id');

        $this->assertPropertyEquals('client_id', 'client_id');
    }

    /**
     * Test the fluid interface of set_client_secret().
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::set_client_secret
     */
    public function testSetClientSecretReturnsSelfReference(): void
    {
        $this->assertEquals($this->class, $this->class->set_client_secret('client_secret'));
    }

    /**
     * Test that set_oauth_token() sets the client_secret.
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::set_oauth_token
     */
    public function testSetOauthTokenSetsOauthToken(): void
    {
        $this->class->set_oauth_token('token');

        $this->assertPropertyEquals('oauth_token', 'token');
    }

    /**
     * Test the fluid interface of set_type().
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::set_type
     */
    public function testSetTypeReturnsSelfReference(): void
    {
        $this->assertEquals($this->class, $this->class->set_type(WNSType::TILE));
    }

    /**
     * Test that set_type() sets the type if it is valid.
     *
     * @param string $type Valid WNS Type
     *
     * @dataProvider validTypeProvider
     * @covers       Lunr\Vortex\WNS\WNSDispatcher::set_type
     */
    public function testSetTypeSetsValidType(string $type): void
    {
        $this->class->set_type($type);

        $this->assertSame($type, $this->getReflectionPropertyValue('type'));
    }

    /**
     * Test that set_type() ignores the type if it is invalid.
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::set_type
     */
    public function testSetTypeDoesNotSetInvalidType(): void
    {
        $this->class->set_type('Android');

        $this->assertEquals(WNSType::RAW, $this->getReflectionPropertyValue('type'));
    }

}

?>
