<?php

/**
 * This file contains the JPushReportBaseTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2023 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush\Tests;

use Lunr\Halo\PropertyTraits\PsrLoggerTestTrait;

/**
 * This class contains test for the constructor of the JPushReport class.
 *
 * @covers \Lunr\Vortex\JPush\JPushReport
 */
class JPushReportBaseTest extends JPushReportTestCase
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
     * Test that the auth token is set to an empty string by default.
     */
    public function testStatusesIsEmptyArray(): void
    {
        $this->assertPropertyEquals('statuses', []);
    }

    /**
     * Test auth_token is initialized as NULL.
     */
    public function testAuthTokenIsInitializedAsNull(): void
    {
        $this->assertNull($this->getReflectionPropertyValue('authToken'));
    }

    /**
     * Test the set_auth_token() sets auth token.
     *
     * @covers \Lunr\Vortex\JPush\JPushReport::set_auth_token
     */
    public function testSetAuthTokenSetsAuthToken(): void
    {
        $this->class->set_auth_token('auth_token_24412');

        $this->assertPropertySame('authToken', 'auth_token_24412');
    }

}

?>
