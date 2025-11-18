<?php

/**
 * This file contains the FCMDispatcherGetOAuthTokenTest class.
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
class FCMDispatcherGetOAuthTokenTest extends FCMDispatcherTestCase
{

    /**
     * Test that get_oauth_token() fails
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::get_oauth_token
     */
    public function testGetOAuthTokenThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid oauth lifetime!');

        $this->tokenBuilder->shouldReceive('issuedBy')
                           ->never();

        $this->http->expects($this->never())
                   ->method('post');

        $this->logger->expects('warning')
                     ->never();

        $this->class->get_oauth_token('invalid relative time');
    }

    /**
     * Test get_oauth_token fails when clientEmail is NULL.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::get_oauth_token
     */
    public function testGetOAuthTokenFailsWhenClientEmailIsNull(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Requesting token failed: No client email provided');

        $this->tokenBuilder->shouldReceive('issuedBy')
                           ->never();

        $this->http->expects($this->never())
                   ->method('post');

        $this->logger->expects('warning')
                     ->never();

        $this->class->get_oauth_token();
    }

    /**
     * Test get_oauth_token fails when privateKey is NULL.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::get_oauth_token
     */
    public function testGetOAuthTokenFailsWhenPrivateKeyIsNull(): void
    {
        $this->setReflectionPropertyValue('clientEmail', 'email_client');

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Requesting token failed: No private key provided');

        $this->tokenBuilder->shouldReceive('issuedBy')
                           ->never();

        $this->http->expects($this->never())
                   ->method('post');

        $this->logger->expects('warning')
                     ->never();

        $this->class->get_oauth_token();
    }

    /**
     * Test get_oauth_token when fetching token fails.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::get_oauth_token
     */
    public function testGetOAuthTokenWhenFetchingTokenFails(): void
    {
        if (!extension_loaded('uopz'))
        {
            $this->markTestSkipped('The uopz extension is not available.');
        }

        $this->setReflectionPropertyValue('clientEmail', 'email_client');
        $this->setReflectionPropertyValue('privateKey', 'secret_key');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Fetching OAuth token for FCM notification(s) failed');

        $issuedAt  = Mockery::mock(DateTimeImmutable::class);
        $expiresAt = Mockery::mock(DateTimeImmutable::class);

        $issuedAt->expects()
                 ->modify('+10 minutes')
                 ->andReturn($expiresAt);

        uopz_set_mock(DateTimeImmutable::class, $issuedAt);
        uopz_set_mock(Builder::class, $this->tokenBuilder);

        $this->tokenBuilder->expects()
                           ->issuedBy('email_client')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->permittedFor('https://oauth2.googleapis.com/token')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->issuedAt($issuedAt)
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->expiresAt($expiresAt)
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->withClaim('scope', 'https://www.googleapis.com/auth/firebase.messaging')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->withHeader('alg', 'RS2256')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->withHeader('typ', 'JWT')
                           ->andReturnSelf();

        uopz_set_return($this->tokenBuilder::class, 'getToken', $this->tokenPlain);

        $this->tokenPlain->expects($this->once())
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

        $this->class->get_oauth_token();

        uopz_unset_return($this->tokenBuilder::class, 'getToken');
        uopz_unset_mock(DateTimeImmutable::class);
        uopz_unset_mock(Builder::class);
    }

    /**
     * Test get_oauth_token when processing json response fails.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::get_oauth_token
     */
    public function testGetOAuthTokenWhenProcessingJsonResponseFails(): void
    {
        if (!extension_loaded('uopz'))
        {
            $this->markTestSkipped('The uopz extension is not available.');
        }

        $this->setReflectionPropertyValue('clientEmail', 'email_client');
        $this->setReflectionPropertyValue('privateKey', 'secret_key');

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Processing json response for fetching OAuth token for FCM notification(s) failed: Syntax error');

        $issuedAt  = Mockery::mock(DateTimeImmutable::class);
        $expiresAt = Mockery::mock(DateTimeImmutable::class);

        $issuedAt->expects()
                 ->modify('+10 minutes')
                 ->andReturn($expiresAt);

        uopz_set_mock(DateTimeImmutable::class, $issuedAt);
        uopz_set_mock(Builder::class, $this->tokenBuilder);

        $this->tokenBuilder->expects()
                           ->issuedBy('email_client')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->permittedFor('https://oauth2.googleapis.com/token')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->issuedAt($issuedAt)
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->expiresAt($expiresAt)
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->withClaim('scope', 'https://www.googleapis.com/auth/firebase.messaging')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->withHeader('alg', 'RS2256')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->withHeader('typ', 'JWT')
                           ->andReturnSelf();

        uopz_set_return($this->tokenBuilder::class, 'getToken', $this->tokenPlain);

        $this->tokenPlain->expects($this->once())
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

        $this->class->get_oauth_token();

        uopz_unset_return($this->tokenBuilder::class, 'getToken');
        uopz_unset_mock(DateTimeImmutable::class);
        uopz_unset_mock(Builder::class);
    }

    /**
     * Test get_oauth_token when processing response fails with general error.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::get_oauth_token
     */
    public function testGetOAuthTokenFailsWithGeneralError(): void
    {
        if (!extension_loaded('uopz'))
        {
            $this->markTestSkipped('The uopz extension is not available.');
        }

        $this->setReflectionPropertyValue('clientEmail', 'email_client');
        $this->setReflectionPropertyValue('privateKey', 'secret_key');

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Fetching OAuth token for FCM notification(s) failed: No access token in the response body');

        $issuedAt  = Mockery::mock(DateTimeImmutable::class);
        $expiresAt = Mockery::mock(DateTimeImmutable::class);

        $issuedAt->expects()
                 ->modify('+10 minutes')
                 ->andReturn($expiresAt);

        uopz_set_mock(DateTimeImmutable::class, $issuedAt);
        uopz_set_mock(Builder::class, $this->tokenBuilder);

        $this->tokenBuilder->expects()
                           ->issuedBy('email_client')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->permittedFor('https://oauth2.googleapis.com/token')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->issuedAt($issuedAt)
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->expiresAt($expiresAt)
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->withClaim('scope', 'https://www.googleapis.com/auth/firebase.messaging')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->withHeader('alg', 'RS2256')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->withHeader('typ', 'JWT')
                           ->andReturnSelf();

        uopz_set_return($this->tokenBuilder::class, 'getToken', $this->tokenPlain);

        $this->tokenPlain->expects($this->once())
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

        $this->class->get_oauth_token();

        uopz_unset_return($this->tokenBuilder::class, 'getToken');
        uopz_unset_mock(DateTimeImmutable::class);
        uopz_unset_mock(Builder::class);
    }

    /**
     * Test get_oauth_token when processing response fails with upstream error.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::get_oauth_token
     */
    public function testGetOAuthTokenFailsWithUpstreamError(): void
    {
        if (!extension_loaded('uopz'))
        {
            $this->markTestSkipped('The uopz extension is not available.');
        }

        $this->setReflectionPropertyValue('clientEmail', 'email_client');
        $this->setReflectionPropertyValue('privateKey', 'secret_key');

        $content      = file_get_contents(TEST_STATICS . '/Vortex/fcm/oauth_error.json');
        $errorMessage = json_decode($content, TRUE)['error_description'];

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Fetching OAuth token for FCM notification(s) failed: ' . $errorMessage);

        $issuedAt  = Mockery::mock(DateTimeImmutable::class);
        $expiresAt = Mockery::mock(DateTimeImmutable::class);

        $issuedAt->expects()
                 ->modify('+10 minutes')
                 ->andReturn($expiresAt);

        uopz_set_mock(DateTimeImmutable::class, $issuedAt);
        uopz_set_mock(Builder::class, $this->tokenBuilder);

        $this->tokenBuilder->expects()
                           ->issuedBy('email_client')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->permittedFor('https://oauth2.googleapis.com/token')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->issuedAt($issuedAt)
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->expiresAt($expiresAt)
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->withClaim('scope', 'https://www.googleapis.com/auth/firebase.messaging')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->withHeader('alg', 'RS2256')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->withHeader('typ', 'JWT')
                           ->andReturnSelf();

        uopz_set_return($this->tokenBuilder::class, 'getToken', $this->tokenPlain);

        $this->tokenPlain->expects($this->once())
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
                         [ 'error' => $errorMessage ]
                     );

        $this->class->get_oauth_token();

        uopz_unset_return($this->tokenBuilder::class, 'getToken');
        uopz_unset_mock(DateTimeImmutable::class);
        uopz_unset_mock(Builder::class);
    }

    /**
     * Test get_oauth_token when fetching token succeeds.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::get_oauth_token
     */
    public function testGetOAuthTokenWhenFetchingTokenSucceeds(): void
    {
        if (!extension_loaded('uopz'))
        {
            $this->markTestSkipped('The uopz extension is not available.');
        }

        $this->setReflectionPropertyValue('clientEmail', 'email_client');
        $this->setReflectionPropertyValue('privateKey', 'secret_key');

        $issuedAt  = Mockery::mock(DateTimeImmutable::class);
        $expiresAt = Mockery::mock(DateTimeImmutable::class);

        $issuedAt->expects()
                 ->modify('+10 minutes')
                 ->andReturn($expiresAt);

        uopz_set_mock(DateTimeImmutable::class, $issuedAt);
        uopz_set_mock(Builder::class, $this->tokenBuilder);

        $this->tokenBuilder->expects()
                           ->issuedBy('email_client')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->permittedFor('https://oauth2.googleapis.com/token')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->issuedAt($issuedAt)
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->expiresAt($expiresAt)
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->withClaim('scope', 'https://www.googleapis.com/auth/firebase.messaging')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->withHeader('alg', 'RS2256')
                           ->andReturnSelf();

        $this->tokenBuilder->expects()
                           ->withHeader('typ', 'JWT')
                           ->andReturnSelf();

        uopz_set_return($this->tokenBuilder::class, 'getToken', $this->tokenPlain);

        $this->tokenPlain->expects($this->once())
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

        $this->assertSame('oauth_token1', $this->class->get_oauth_token());

        uopz_unset_return($this->tokenBuilder::class, 'getToken');
        uopz_unset_mock(DateTimeImmutable::class);
        uopz_unset_mock(Builder::class);
    }

}

?>
