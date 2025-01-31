<?php

/**
 * This file contains the WNSDispatcherGetOAuthTokenTest Class
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\WNS\Tests;

use WpOrg\Requests\Exception as RequestsException;

/**
 * Class WNSDispatcherGetOAuthTokenTest tests the Authentication to the WNS Server
 * @covers Lunr\Vortex\WNS\WNSDispatcher
 */
class WNSDispatcherGetOAuthTokenTest extends WNSDispatcherTestCase
{

    /**
     * Prepares the configuration for a $config call.
     *
     * @param string $client_id     The client_id you want returned from the config
     * @param string $client_secret The client_secret you want returned
     *
     * @return void
     */
    private function expectFromConfig($client_id, $client_secret): void
    {
        $this->setReflectionPropertyValue('client_id', $client_id);
        $this->setReflectionPropertyValue('client_secret', $client_secret);
    }

    /**
     * Test that using get_oauth_token queries with config variables.
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::get_oauth_token
     */
    public function testGetOauthMakesCorrectRequest(): void
    {
        $request_post = [
            'grant_type'    => 'client_credentials',
            'client_id'     => '012345',
            'client_secret' => '012345678',
            'scope'         => 'notify.windows.com',
        ];

        $headers = [ 'Content-Type' => 'application/x-www-form-urlencoded' ];
        $url     = 'https://login.live.com/accesstoken.srf';

        $this->response->status_code = 200;
        $this->response->body        = '{"access_token":"access_token"}';

        $this->expectFromConfig('012345', '012345678');

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($this->equalTo($url), $this->equalTo($headers), $this->equalTo($request_post))
                   ->will($this->returnValue($this->response));

        $this->class->get_oauth_token();
    }

    /**
     * Test that using get_oauth_token responds with FALSE if the response is erroneous.
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::get_oauth_token
     */
    public function testGetOauthRespondsFalseIfRequestError(): void
    {
        $request_post = [
            'grant_type'    => 'client_credentials',
            'client_id'     => '012345',
            'client_secret' => '012345678',
            'scope'         => 'notify.windows.com',
        ];

        $this->expectFromConfig('012345', '012345678');

        $headers = [ 'Content-Type' => 'application/x-www-form-urlencoded' ];
        $url     = 'https://login.live.com/accesstoken.srf';

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($this->equalTo($url), $this->equalTo($headers), $this->equalTo($request_post))
                   ->will($this->throwException(new RequestsException('Network error!', 'curlerror', NULL)));

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with('Requesting token failed: No response');

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Requesting token failed: No response');

        $this->class->get_oauth_token();
    }

    /**
     * Test that using get_oauth_token responds with FALSE if the response is invalid JSON.
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::get_oauth_token
     */
    public function testGetOauthRespondsFalseIfInvalidJSON(): void
    {
        $request_post = [
            'grant_type'    => 'client_credentials',
            'client_id'     => '012345',
            'client_secret' => '012345678',
            'scope'         => 'notify.windows.com',
        ];

        $this->expectFromConfig('012345', '012345678');

        $headers = [ 'Content-Type' => 'application/x-www-form-urlencoded' ];
        $url     = 'https://login.live.com/accesstoken.srf';

        $this->response->status_code = 200;
        $this->response->body        = 'HELLO I\'m an invalid JSON object';

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($this->equalTo($url), $this->equalTo($headers), $this->equalTo($request_post))
                   ->will($this->returnValue($this->response));

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with('Requesting token failed: Malformed JSON response');

        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Requesting token failed: Malformed JSON response');

        $this->class->get_oauth_token();
    }

    /**
     * Test that using get_oauth_token responds with FALSE if the response is incomplete JSON.
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::get_oauth_token
     */
    public function testGetOauthRespondsFalseIfIncompleteJSON(): void
    {
        $request_post = [
            'grant_type'    => 'client_credentials',
            'client_id'     => '012345',
            'client_secret' => '012345678',
            'scope'         => 'notify.windows.com',
        ];

        $this->expectFromConfig('012345', '012345678');

        $headers = [ 'Content-Type' => 'application/x-www-form-urlencoded' ];
        $url     = 'https://login.live.com/accesstoken.srf';

        $this->response->status_code = 200;
        $this->response->body        = '{"Message": "HELLO I\'m an invalid JSON object"}';

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($this->equalTo($url), $this->equalTo($headers), $this->equalTo($request_post))
                   ->will($this->returnValue($this->response));

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with('Requesting token failed: Not a valid JSON response');

        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Requesting token failed: Not a valid JSON response');

        $this->class->get_oauth_token();
    }

    /**
     * Test that using get_oauth_token response.
     *
     * @covers Lunr\Vortex\WNS\WNSDispatcher::get_oauth_token
     */
    public function testGetOauthRespondsCorrectly(): void
    {
        $request_post = [
            'grant_type'    => 'client_credentials',
            'client_id'     => '012345',
            'client_secret' => '012345678',
            'scope'         => 'notify.windows.com',
        ];

        $this->expectFromConfig('012345', '012345678');

        $headers = [ 'Content-Type' => 'application/x-www-form-urlencoded' ];
        $url     = 'https://login.live.com/accesstoken.srf';

        $this->response->status_code = 200;
        $this->response->body        = '{"access_token":"access_token"}';

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($this->equalTo($url), $this->equalTo($headers), $this->equalTo($request_post))
                   ->will($this->returnValue($this->response));

        $this->assertSame('access_token', $this->class->get_oauth_token());
    }

}

?>
