<?php

/**
 * This file contains the FCMDispatcherConfigureOAuthTokenTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use BadMethodCallException;
use DateTimeImmutable;
use InvalidArgumentException;
use Lcobucci\JWT\Token\Builder;
use Mockery;
use RuntimeException;
use UnexpectedValueException;
use WpOrg\Requests\Exception as RequestsException;
use WpOrg\Requests\Response;

/**
 * This class contains tests for the setters of the FCMDispatcher class.
 *
 * @covers Lunr\Vortex\FCM\FCMDispatcher
 */
class FCMDispatcherConfigureOAuthTokenTest extends FCMDispatcherTestCase
{

    /**
     * Test that configure_oauth_token() fails
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::configure_oauth_token
     */
    public function testConfigureOAuthTokenThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid oauth lifetime!');

        $this->token_builder->shouldReceive('issuedBy')
                            ->never();

        $this->http->expects($this->never())
                   ->method('post');

        $this->logger->expects('warning')
                     ->never();

        $this->class->configure_oauth_token('invalid relative time');
    }

    /**
     * Test configure_oauth_token fails when client_email is NULL.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::configure_oauth_token
     */
    public function testConfigureOAuthTokenFailsWhenClientEmailIsNull(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Requesting token failed: No client email provided');

        $this->token_builder->shouldReceive('issuedBy')
                            ->never();

        $this->http->expects($this->never())
                   ->method('post');

        $this->logger->expects('warning')
                     ->never();

        $this->class->configure_oauth_token();

        $this->assertPropertyEquals('oauth_token', NULL);
    }

    /**
     * Test configure_oauth_token fails when private_key is NULL.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::configure_oauth_token
     */
    public function testConfigureOAuthTokenFailsWhenPrivateKeyIsNull(): void
    {
        $this->setReflectionPropertyValue('client_email', 'email_client');

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Requesting token failed: No private key provided');

        $this->token_builder->shouldReceive('issuedBy')
                            ->never();

        $this->http->expects($this->never())
                   ->method('post');

        $this->logger->expects('warning')
                     ->never();

        $this->class->configure_oauth_token();

        $this->assertPropertyEquals('oauth_token', NULL);
    }

    /**
     * Test configure_oauth_token when fetching token fails.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::configure_oauth_token
     */
    public function testConfigureOAuthTokenWhenFetchingTokenFails(): void
    {
        if (!extension_loaded('uopz'))
        {
            $this->markTestSkipped('The uopz extension is not available.');
        }

        $this->setReflectionPropertyValue('client_email', 'email_client');
        $this->setReflectionPropertyValue('private_key', 'secret_key');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Fetching OAuth token for FCM notification(s) failed');

        $issued_at  = Mockery::mock(DateTimeImmutable::class);
        $expires_at = Mockery::mock(DateTimeImmutable::class);

        $issued_at->expects()
                  ->modify('+10 minutes')
                  ->andReturn($expires_at);

        uopz_set_mock(DateTimeImmutable::class, $issued_at);
        uopz_set_mock(Builder::class, $this->token_builder);

        $this->token_builder->expects()
                            ->issuedBy('email_client')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->permittedFor('https://oauth2.googleapis.com/token')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->issuedAt($issued_at)
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->expiresAt($expires_at)
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->withClaim('scope', 'https://www.googleapis.com/auth/firebase.messaging')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->withHeader('alg', 'RS2256')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->withHeader('typ', 'JWT')
                            ->andReturnSelf();

        uopz_set_return($this->token_builder::class, 'getToken', $this->token_plain);

        $this->token_plain->expects($this->once())
                          ->method('toString')
                          ->willReturn('jwt_token');

        $headers = [
            'Content-Type'  => 'application/json'
        ];

        $payload = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => 'jwt_token',
        ];

        $this->http->expects($this->once())
                   ->method('post')
                   ->with('https://oauth2.googleapis.com/token', $headers, json_encode($payload, JSON_UNESCAPED_UNICODE), [])
                   ->willThrowException(new RequestsException('cURL error 10: Request error', 'curlerror', NULL));

        $this->logger->expects('warning')
                     ->once()
                     ->with('Fetching OAuth token for FCM notification(s) failed: {message}', [ 'message' => 'cURL error 10: Request error' ]);

        $this->class->configure_oauth_token('+10 minutes');

        $this->assertPropertyEquals('oauth_token', NULL);

        uopz_unset_return($this->token_builder::class, 'getToken');
        uopz_unset_mock(DateTimeImmutable::class);
        uopz_unset_mock(Builder::class);
    }

    /**
     * Test configure_oauth_token when processing json response fails.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::configure_oauth_token
     */
    public function testConfigureOAuthTokenWhenProcessingJsonResponseFails(): void
    {
        if (!extension_loaded('uopz'))
        {
            $this->markTestSkipped('The uopz extension is not available.');
        }

        $this->setReflectionPropertyValue('client_email', 'email_client');
        $this->setReflectionPropertyValue('private_key', 'secret_key');

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Processing json response for fetching OAuth token for FCM notification(s) failed: Syntax error');

        $issued_at  = Mockery::mock(DateTimeImmutable::class);
        $expires_at = Mockery::mock(DateTimeImmutable::class);

        $issued_at->expects()
                  ->modify('+10 minutes')
                  ->andReturn($expires_at);

        uopz_set_mock(DateTimeImmutable::class, $issued_at);
        uopz_set_mock(Builder::class, $this->token_builder);

        $this->token_builder->expects()
                            ->issuedBy('email_client')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->permittedFor('https://oauth2.googleapis.com/token')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->issuedAt($issued_at)
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->expiresAt($expires_at)
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->withClaim('scope', 'https://www.googleapis.com/auth/firebase.messaging')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->withHeader('alg', 'RS2256')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->withHeader('typ', 'JWT')
                            ->andReturnSelf();

        uopz_set_return($this->token_builder::class, 'getToken', $this->token_plain);

        $this->token_plain->expects($this->once())
                          ->method('toString')
                          ->willReturn('jwt_token');

        $headers = [
            'Content-Type'  => 'application/json'
        ];

        $payload = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => 'jwt_token',
        ];

        $response = new Response();

        $response->body = '{';

        $this->http->expects($this->once())
                   ->method('post')
                   ->with('https://oauth2.googleapis.com/token', $headers, json_encode($payload, JSON_UNESCAPED_UNICODE), [])
                   ->willReturn($response);

        $this->logger->expects('warning')
                     ->once()
                     ->with(
                         'Processing json response for fetching OAuth token for FCM notification(s) failed: {message}',
                         [ 'message' => 'Syntax error' ]
                     );

        $this->class->configure_oauth_token();

        $this->assertPropertyEquals('oauth_token', NULL);

        uopz_unset_return($this->token_builder::class, 'getToken');
        uopz_unset_mock(DateTimeImmutable::class);
        uopz_unset_mock(Builder::class);
    }

    /**
     * Test configure_oauth_token when processing response fails with general error.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::configure_oauth_token
     */
    public function testConfigureOAuthTokenFailsWithGeneralError(): void
    {
        if (!extension_loaded('uopz'))
        {
            $this->markTestSkipped('The uopz extension is not available.');
        }

        $this->setReflectionPropertyValue('client_email', 'email_client');
        $this->setReflectionPropertyValue('private_key', 'secret_key');

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Fetching OAuth token for FCM notification(s) failed: No access token in the response body');

        $issued_at  = Mockery::mock(DateTimeImmutable::class);
        $expires_at = Mockery::mock(DateTimeImmutable::class);

        $issued_at->expects()
                  ->modify('+10 minutes')
                  ->andReturn($expires_at);

        uopz_set_mock(DateTimeImmutable::class, $issued_at);
        uopz_set_mock(Builder::class, $this->token_builder);

        $this->token_builder->expects()
                            ->issuedBy('email_client')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->permittedFor('https://oauth2.googleapis.com/token')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->issuedAt($issued_at)
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->expiresAt($expires_at)
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->withClaim('scope', 'https://www.googleapis.com/auth/firebase.messaging')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->withHeader('alg', 'RS2256')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->withHeader('typ', 'JWT')
                            ->andReturnSelf();

        uopz_set_return($this->token_builder::class, 'getToken', $this->token_plain);

        $this->token_plain->expects($this->once())
                          ->method('toString')
                          ->willReturn('jwt_token');

        $headers = [
            'Content-Type'  => 'application/json'
        ];

        $payload = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => 'jwt_token',
        ];

        $response = new Response();

        $response->body = '{"token":"oauth_token1"}';

        $this->http->expects($this->once())
                   ->method('post')
                   ->with('https://oauth2.googleapis.com/token', $headers, json_encode($payload, JSON_UNESCAPED_UNICODE), [])
                   ->willReturn($response);

        $this->logger->expects('warning')
                     ->once()
                     ->with(
                         'Fetching OAuth token for FCM notification(s) failed: {error}',
                         [ 'error' => 'No access token in the response body' ]
                     );

        $this->class->configure_oauth_token();

        $this->assertPropertyEquals('oauth_token', NULL);

        uopz_unset_return($this->token_builder::class, 'getToken');
        uopz_unset_mock(DateTimeImmutable::class);
        uopz_unset_mock(Builder::class);
    }

    /**
     * Test Get_oauth_token when processing response fails with upstream error.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::configure_oauth_token
     */
    public function testConfigureOAuthTokenFailsWithUpstreamError(): void
    {
        if (!extension_loaded('uopz'))
        {
            $this->markTestSkipped('The uopz extension is not available.');
        }

        $this->setReflectionPropertyValue('client_email', 'email_client');
        $this->setReflectionPropertyValue('private_key', 'secret_key');

        $content   = file_get_contents(TEST_STATICS . '/Vortex/fcm/oauth_error.json');
        $error_msg = json_decode($content, TRUE)['error_description'];

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Fetching OAuth token for FCM notification(s) failed: ' . $error_msg);

        $issued_at  = Mockery::mock(DateTimeImmutable::class);
        $expires_at = Mockery::mock(DateTimeImmutable::class);

        $issued_at->expects()
                  ->modify('+10 minutes')
                  ->andReturn($expires_at);

        uopz_set_mock(DateTimeImmutable::class, $issued_at);
        uopz_set_mock(Builder::class, $this->token_builder);

        $this->token_builder->expects()
                            ->issuedBy('email_client')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->permittedFor('https://oauth2.googleapis.com/token')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->issuedAt($issued_at)
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->expiresAt($expires_at)
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->withClaim('scope', 'https://www.googleapis.com/auth/firebase.messaging')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->withHeader('alg', 'RS2256')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->withHeader('typ', 'JWT')
                            ->andReturnSelf();

        uopz_set_return($this->token_builder::class, 'getToken', $this->token_plain);

        $this->token_plain->expects($this->once())
                          ->method('toString')
                          ->willReturn('jwt_token');

        $headers = [
            'Content-Type'  => 'application/json'
        ];

        $payload = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => 'jwt_token',
        ];

        $response = new Response();

        $response->body = $content;

        $this->http->expects($this->once())
                   ->method('post')
                   ->with('https://oauth2.googleapis.com/token', $headers, json_encode($payload, JSON_UNESCAPED_UNICODE), [])
                   ->willReturn($response);

        $this->logger->expects('warning')
                     ->once()
                     ->with(
                         'Fetching OAuth token for FCM notification(s) failed: {error}',
                         [ 'error' => $error_msg ]
                     );

        $this->class->configure_oauth_token();

        $this->assertPropertyEquals('oauth_token', NULL);

        uopz_unset_return($this->token_builder::class, 'getToken');
        uopz_unset_mock(DateTimeImmutable::class);
        uopz_unset_mock(Builder::class);
    }

    /**
     * Test configure_oauth_token when fetching token succeeds.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::configure_oauth_token
     */
    public function testConfigureOAuthTokenWhenFetchingTokenSucceeds(): void
    {
        if (!extension_loaded('uopz'))
        {
            $this->markTestSkipped('The uopz extension is not available.');
        }

        $this->setReflectionPropertyValue('client_email', 'email_client');
        $this->setReflectionPropertyValue('private_key', 'secret_key');

        $issued_at  = Mockery::mock(DateTimeImmutable::class);
        $expires_at = Mockery::mock(DateTimeImmutable::class);

        $issued_at->expects()
                  ->modify('+10 minutes')
                  ->andReturn($expires_at);

        uopz_set_mock(DateTimeImmutable::class, $issued_at);
        uopz_set_mock(Builder::class, $this->token_builder);

        $this->token_builder->expects()
                            ->issuedBy('email_client')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->permittedFor('https://oauth2.googleapis.com/token')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->issuedAt($issued_at)
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->expiresAt($expires_at)
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->withClaim('scope', 'https://www.googleapis.com/auth/firebase.messaging')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->withHeader('alg', 'RS2256')
                            ->andReturnSelf();

        $this->token_builder->expects()
                            ->withHeader('typ', 'JWT')
                            ->andReturnSelf();

        uopz_set_return($this->token_builder::class, 'getToken', $this->token_plain);

        $this->token_plain->expects($this->once())
                          ->method('toString')
                          ->willReturn('jwt_token');

        $headers = [
            'Content-Type'  => 'application/json'
        ];

        $payload = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => 'jwt_token',
        ];

        $response = new Response();

        $response->body = '{"access_token":"oauth_token1"}';

        $this->http->expects($this->once())
                   ->method('post')
                   ->with('https://oauth2.googleapis.com/token', $headers, json_encode($payload, JSON_UNESCAPED_UNICODE), [])
                   ->willReturn($response);

        $this->class->configure_oauth_token();

        $this->assertPropertyEquals('oauth_token', 'oauth_token1');

        uopz_unset_return($this->token_builder::class, 'getToken');
        uopz_unset_mock(DateTimeImmutable::class);
        uopz_unset_mock(Builder::class);
    }

}

?>
