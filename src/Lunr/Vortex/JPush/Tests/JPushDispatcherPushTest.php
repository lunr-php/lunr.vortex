<?php

/**
 * This file contains the JPushDispatcherPushTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2020 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush\Tests;

use Lunr\Vortex\APNS\APNSAlertPayload;
use Lunr\Vortex\Email\EmailPayload;
use Lunr\Vortex\FCM\FCMPayload;
use Lunr\Vortex\PushNotificationStatus;
use Lunr\Vortex\WNS\WNSTilePayload;
use WpOrg\Requests\Exception as RequestsException;
use WpOrg\Requests\Response;

/**
 * This class contains test for the push() method of the JPushDispatcher class.
 *
 * @covers \Lunr\Vortex\JPush\JPushDispatcher
 */
class JPushDispatcherPushTest extends JPushDispatcherTestCase
{

    /**
     * Unit test data provider for unsupported payload objects.
     *
     * @return array Unsupported payload objects
     */
    public static function unsupportedPayloadProvider(): array
    {
        $data          = [];
        $data['apns']  = [ new APNSAlertPayload() ];
        $data['email'] = [ new EmailPayload() ];
        $data['fcm']   = [ new FCMPayload() ];
        $data['wns']   = [ new WNSTilePayload() ];

        return $data;
    }

    /**
     * Test that push() throws an exception is the passed payload object is not supported.
     *
     * @param object $payload Unsupported payload object
     *
     * @dataProvider unsupportedPayloadProvider
     * @covers       \Lunr\Vortex\JPush\JPushDispatcher::push
     */
    public function testPushingWithUnsupportedPayloadThrowsException(object $payload): void
    {
        $endpoints = [ 'endpoint' ];

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid payload object!');

        $this->class->push($payload, $endpoints);
    }

    /**
     * Test that push() returns JPushResponseObject.
     *
     * @covers \Lunr\Vortex\JPush\JPushDispatcher::push
     */
    public function testPushReturnsJPushResponseObject(): void
    {
        $endpoints = [];

        $this->redefineConstant('Lunr\Vortex\JPush\JPushDispatcher::BATCH_SIZE', 2);

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\JPush\JPushResponse', $result);
    }

    /**
     * Test that push_batch() returns JPushBatchResponse.
     *
     * @covers \Lunr\Vortex\JPush\JPushDispatcher::push_batch
     */
    public function testPushBatchReturnsJPushBatchResponseObject(): void
    {
        $endpoints = [ 'endpoint' ];

        $response = $this->getMockBuilder(Response::class)->getMock();

        $this->logger->expects('warning')
                     ->once()
                     ->withAnyArgs();

        $this->http->expects('post')
                   ->once()
                   ->andReturn($response);

        $method = $this->getReflectionMethod('push_batch');
        $result = $method->invokeArgs($this->class, [ $this->payload, &$endpoints ]);

        $this->assertInstanceOf('Lunr\Vortex\JPush\JPushBatchResponse', $result);
    }

    /**
     * Test that push() doesn't send any request if no endpoint is set.
     *
     * @covers \Lunr\Vortex\JPush\JPushDispatcher::push
     */
    public function testPushDoesNoRequestIfNoEndpoint(): void
    {
        $endpoints = [];

        $this->http->expects('post')->never();

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that push() resets the properties after a push.
     *
     * @covers \Lunr\Vortex\JPush\JPushDispatcher::push
     */
    public function testPushResetsProperties(): void
    {
        $endpoints = [ 'endpoint' ];

        $this->payload->expects($this->exactly(1))
                      ->method('get_payload')
                      ->willReturn([ 'collapse_key' => 'abcde-12345' ]);

        $this->setReflectionPropertyValue('auth_token', 'auth_token');

        $response = $this->getMockBuilder(Response::class)->getMock();

        $this->logger->expects('warning')
                     ->once()
                     ->withAnyArgs();

        $this->http->expects('post')
                   ->once()
                   ->andReturn($response);

        $this->class->push($this->payload, $endpoints);

        $this->assertPropertyEquals('auth_token', 'auth_token');
    }

    /**
     * Test that push() works as expected when the request failed.
     *
     * @covers \Lunr\Vortex\JPush\JPushDispatcher::push
     */
    public function testPushWithFailedRequest(): void
    {
        $this->mockFunction('curl_errno', function () { return 10; });

        $endpoints = [ 'endpoint' ];
        $url       = 'https://api.jpush.cn/v3/push';
        $post      = '{"alert":"hello","audience":{"registration_id":["endpoint"]}}';
        $payload   = [ 'alert' => 'hello' ];
        $options   = [ 'timeout' => 15, 'connect_timeout' => 15 ];

        $this->payload->expects($this->exactly(1))
                      ->method('get_payload')
                      ->willReturn($payload);

        $this->http->expects('post')
                   ->once()
                   ->with($url, [], $post, $options)
                   ->andThrow(new RequestsException('cURL error 10: Request error', 'curlerror', NULL));

        $context = [ 'message' => 'cURL error 10: Request error' ];

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching JPush notification(s) failed: {message}', $context);

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching JPush notification failed: {error}', [ 'error' => 'Unknown error' ]);

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\JPush\JPushResponse', $result);

        $this->unmockFunction('curl_errno');
    }

    /**
     * Test that push() works as expected when the request failed.
     *
     * @covers \Lunr\Vortex\JPush\JPushDispatcher::push
     */
    public function testPushWithTimeoutRequest()
    {
        $this->mockFunction('curl_errno', function () { return 28; });

        $endpoints = [ 'endpoint' ];
        $url       = 'https://api.jpush.cn/v3/push';
        $post      = '{"alert":"hello","audience":{"registration_id":["endpoint"]}}';
        $payload   = [ 'alert' => 'hello' ];
        $options   = [ 'timeout' => 15, 'connect_timeout' => 15 ];

        $this->payload->expects($this->exactly(1))
                      ->method('get_payload')
                      ->willReturn($payload);

        $this->http->expects('post')
                   ->once()
                   ->with($url, [], $post, $options)
                   ->andThrow(new RequestsException('cURL error 28: Request timed out', 'curlerror', NULL));

        $context = [ 'message' => 'cURL error 28: Request timed out' ];

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching JPush notification(s) failed: {message}', $context);

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching JPush notification failed: {error}', [ 'error' => 'Internal error' ]);

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\JPush\JPushResponse', $result);

        $this->assertSame($result->get_status('endpoint'), PushNotificationStatus::TemporaryError);

        $this->unmockFunction('curl_errno');
    }

    /**
     * Test that push() works as expected when the request failed.
     *
     * @covers \Lunr\Vortex\JPush\JPushDispatcher::push
     */
    public function testPushWithFailureResponse()
    {
        $endpoints = [ 'endpoint' ];
        $url       = 'https://api.jpush.cn/v3/push';
        $post      = '{"alert":"hello","audience":{"registration_id":["endpoint"]}}';
        $payload   = [ 'alert' => 'hello' ];
        $options   = [ 'timeout' => 15, 'connect_timeout' => 15 ];

        $this->payload->expects($this->exactly(1))
                      ->method('get_payload')
                      ->willReturn($payload);

        $response              = $this->getMockBuilder(Response::class)->getMock();
        $response->success     = FALSE;
        $response->status_code = 400;

        $this->http->expects('post')
                   ->once()
                   ->with($url, [], $post, $options)
                   ->andReturn($response);

        $message = 'Dispatching JPush notification failed: {error}';
        $context = [ 'error' => 'Invalid request' ];

        $this->logger->expects('warning')
                     ->once()
                     ->with($message, $context);

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\JPush\JPushResponse', $result);

        $this->assertSame(PushNotificationStatus::Error, $result->get_status('endpoint'));
    }

    /**
     * Test that push() sends correct request with no properties set except the endpoint.
     *
     * @covers \Lunr\Vortex\JPush\JPushDispatcher::push
     */
    public function testPushRequestWithDefaultValues(): void
    {
        $endpoints = [ 'endpoint' ];
        $url       = 'https://api.jpush.cn/v3/push';
        $post      = '{"alert":"hello","audience":{"registration_id":["endpoint"]}}';
        $payload   = [ 'alert' => 'hello' ];
        $options   = [ 'timeout' => 15, 'connect_timeout' => 15 ];

        $this->payload->expects($this->exactly(1))
                      ->method('get_payload')
                      ->willReturn($payload);

        $response = $this->getMockBuilder(Response::class)->getMock();

        $this->logger->expects('warning')
                     ->once()
                     ->withAnyArgs();

        $this->http->expects('post')
                   ->once()
                   ->with($url, [], $post, $options)
                   ->andReturn($response);

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that push() sends correct request with single endpoint.
     *
     * @covers \Lunr\Vortex\JPush\JPushDispatcher::push
     */
    public function testPushRequestWithSingleEndpoint(): void
    {
        $endpoints = [ 'endpoint' ];
        $url       = 'https://api.jpush.cn/v3/push';
        $post      = '{"collapse_key":"abcde-12345","alert":"hello","audience":{"registration_id":["endpoint"]}}';
        $payload   = [ 'collapse_key' => 'abcde-12345', 'alert' => 'hello' ];
        $options   = [ 'timeout' => 15, 'connect_timeout' => 15 ];

        $this->payload->expects($this->exactly(1))
                      ->method('get_payload')
                      ->willReturn($payload);

        $this->setReflectionPropertyValue('auth_token', 'auth_token');

        $response = $this->getMockBuilder(Response::class)->getMock();

        $this->logger->expects('warning')
                     ->once()
                     ->withAnyArgs();

        $this->http->expects('post')
                   ->once()
                   ->with($url, [], $post, $options)
                   ->andReturn($response);

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that push() generates a payload that doesn't encode unicode characters.
     *
     * @covers \Lunr\Vortex\JPush\JPushDispatcher::push
     */
    public function testPushRequestWithMultibyteCharacters(): void
    {
        $endpoints = [ 'endpoint' ];
        $url       = 'https://api.jpush.cn/v3/push';
        $post      = '{"collapse_key":"abcde-12345","alert":"hello","message":{"msg_content":"凄い"},"audience":{"registration_id":["endpoint"]}}';
        $payload   = [ 'collapse_key' => 'abcde-12345', 'alert' => 'hello', 'message' => [ 'msg_content' => '凄い' ] ];
        $options   = [ 'timeout' => 15, 'connect_timeout' => 15 ];

        $this->payload->expects($this->exactly(1))
                      ->method('get_payload')
                      ->willReturn($payload);

        $this->setReflectionPropertyValue('auth_token', 'auth_token');

        $response = $this->getMockBuilder(Response::class)->getMock();

        $this->logger->expects('warning')
                     ->once()
                     ->withAnyArgs();

        $this->http->expects('post')
                   ->once()
                   ->with($url, [], $post, $options)
                   ->andReturn($response);

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that push() sends correct request with multiple endpoints within one batch.
     *
     * @covers \Lunr\Vortex\JPush\JPushDispatcher::push
     */
    public function testPushRequestWithMultipleEndpointsOneBatch(): void
    {
        $endpoints = [ 'endpoint1', 'endpoint2' ];
        $url       = 'https://api.jpush.cn/v3/push';
        $post      = '{"collapse_key":"abcde-12345","alert":"hello","audience":{"registration_id":["endpoint1","endpoint2"]}}';
        $payload   = [ 'collapse_key' => 'abcde-12345', 'alert' => 'hello' ];

        $this->payload->expects($this->exactly(1))
                      ->method('get_payload')
                      ->willReturn($payload);

        $this->setReflectionPropertyValue('auth_token', 'auth_token');

        $response = $this->getMockBuilder(Response::class)->getMock();

        $options = [
            'timeout'         => 15,
            'connect_timeout' => 15
        ];

        $this->logger->expects('warning')
                     ->once()
                     ->withAnyArgs();

        $this->http->expects('post')
                   ->once()
                   ->with($url, [], $post, $options)
                   ->andReturn($response);

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that push() sends correct request with multiple endpoints within multiple batches.
     *
     * @covers \Lunr\Vortex\JPush\JPushDispatcher::push
     */
    public function testPushRequestWithMultipleEndpointsMultipleBatches(): void
    {
        $endpoints = [ 'endpoint1', 'endpoint2', 'endpoint3', 'endpoint4', 'endpoint5' ];

        $this->setReflectionPropertyValue('auth_token', 'auth_token');

        $response = $this->getMockBuilder(Response::class)->getMock();

        $url = 'https://api.jpush.cn/v3/push';

        $post1   = '{"collapse_key":"abcde-12345","alert":"hello","audience":{"registration_id":["endpoint1","endpoint2"]}}';
        $post2   = '{"collapse_key":"abcde-12345","alert":"hello","audience":{"registration_id":["endpoint3","endpoint4"]}}';
        $post3   = '{"collapse_key":"abcde-12345","alert":"hello","audience":{"registration_id":["endpoint5"]}}';
        $payload = [ 'collapse_key' => 'abcde-12345', 'alert' => 'hello' ];
        $options = [ 'timeout' => 15, 'connect_timeout' => 15 ];

        $this->payload->expects($this->exactly(3))
                      ->method('get_payload')
                      ->willReturn($payload);

        $this->logger->expects('warning')
                     ->times(3)
                     ->withAnyArgs();

        $this->http->expects('post')
                   ->once()
                   ->with($url, [], $post1, $options)
                   ->andReturn($response);

        $this->http->expects('post')
                   ->once()
                   ->with($url, [], $post2, $options)
                   ->andReturn($response);

        $this->http->expects('post')
                   ->once()
                   ->with($url, [], $post3, $options)
                   ->andReturn($response);

        $this->class->push($this->payload, $endpoints);
    }

}

?>
