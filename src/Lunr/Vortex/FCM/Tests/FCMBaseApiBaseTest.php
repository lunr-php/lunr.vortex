<?php

/**
 * This file contains the FCMBaseApiBaseTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use Lunr\Halo\PropertyTraits\PsrLoggerTestTrait;

/**
 * This class contains test for the constructor of the FCMBaseApi class.
 *
 * @covers Lunr\Vortex\FCM\FCMBaseApi
 */
class FCMBaseApiBaseTest extends FCMBaseApiTest
{

    use PsrLoggerTestTrait;

    /**
     * Test that the passed Requests\Session object is set correctly.
     */
    public function testRequestsSessionIsSetCorrectly(): void
    {
        $this->assertPropertySame('http', $this->http);
    }

    /**
     * Test that the OAuth token is set to null by default.
     */
    public function testOAuthTokenIsSetToNull(): void
    {
        $this->assertPropertyEquals('oauth_token', NULL);
    }

    /**
     * Test that the project_id is set to null by default.
     */
    public function testProjectIdIsSetToNull(): void
    {
        $this->assertPropertyEquals('project_id', NULL);
    }

    /**
     * Test that the client_email is set to null by default.
     */
    public function testClientEmailIsSetToNull(): void
    {
        $this->assertPropertyEquals('client_email', NULL);
    }

    /**
     * Test that the private_key is set to null by default.
     */
    public function testPrivateKeyIsSetToNull(): void
    {
        $this->assertPropertyEquals('private_key', NULL);
    }

}

?>
