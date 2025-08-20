<?php

/**
 * This file contains the FCMDispatcherPushTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2017 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use Lunr\Vortex\APNS\APNSAlertPayload;
use Lunr\Vortex\Email\EmailPayload;
use Lunr\Vortex\JPush\JPushMessagePayload;
use Lunr\Vortex\PushNotificationStatus;
use Lunr\Vortex\WNS\WNSTilePayload;
use WpOrg\Requests\Exception as RequestsException;
use WpOrg\Requests\Response;

/**
 * This class contains test for the push() method of the FCMDispatcher class.
 *
 * @covers Lunr\Vortex\FCM\FCMDispatcher
 */
class FCMDispatcherPushTest extends FCMDispatcherTestCase
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
        $data['jpush'] = [ new JPushMessagePayload() ];
        $data['wns']   = [ new WNSTilePayload() ];

        return $data;
    }

    /**
     * Test that push() throws an exception is the passed payload object is not supported.
     *
     * @param object $payload Unsupported payload object
     *
     * @dataProvider unsupportedPayloadProvider
     * @covers       Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushingWithUnsupportedPayloadThrowsException(object $payload): void
    {
        $endpoints = [ 'endpoint' ];

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid payload object!');

        $this->class->push($payload, $endpoints);
    }

    /**
     * Test that push() throws exception when no endpoints are provided.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushWithEmptyEndpointsAndNoOtherTargetThrowException(): void
    {
        $endpoints = [];

        $this->payload->expects('is_broadcast')
                      ->once()
                      ->andReturn(FALSE);

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('No target provided!');

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that push() returns response when oauth_token is null.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushWhenOAuthTokenIsNullSingleEndpoint(): void
    {
        $endpoints = [ 'endpoint' ];

        $this->setReflectionPropertyValue('oauth_token', NULL);

        $this->logger->expects('warning')
                     ->once()
                     ->with('Tried to push FCM notification but wasn\'t authenticated.');

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                        [ 'endpoint' => 'endpoint', 'error' => 'Error with authentication' ]
                     );

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\FCM\FCMResponse', $result);
    }

    /**
     * Test that push() returns response when oauth_token is null.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushWhenOAuthTokenIsNullMultipleEndpoints(): void
    {
        $endpoints = [ 'endpoint', 'endpoint1', 'endpoint2' ];

        $this->setReflectionPropertyValue('oauth_token', NULL);

        $this->logger->expects('warning')
                     ->once()
                     ->with('Tried to push FCM notification but wasn\'t authenticated.');

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                        [ 'endpoint' => 'endpoint', 'error' => 'Error with authentication' ]
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                        [ 'endpoint' => 'endpoint1', 'error' => 'Error with authentication' ]
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                        [ 'endpoint' => 'endpoint2', 'error' => 'Error with authentication' ]
                     );

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\FCM\FCMResponse', $result);
    }

    /**
     * Test that push() returns response when project_id is null.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushWhenProjectIdIsNullSingleEndpoint(): void
    {
        $endpoints = [ 'endpoint' ];

        $this->setReflectionPropertyValue('oauth_token', 'test');
        $this->setReflectionPropertyValue('project_id', NULL);

        $this->logger->expects('warning')
                     ->once()
                     ->with('Tried to push FCM notification but project id is not provided.');

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                        [ 'endpoint' => 'endpoint', 'error' => 'Invalid argument' ]
                     );

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\FCM\FCMResponse', $result);
    }

    /**
     * Test that push() returns response when project_id is null.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushWhenProjectIdIsNullMultipleEndpoints(): void
    {
        $endpoints = [ 'endpoint', 'endpoint1', 'endpoint2' ];

        $this->setReflectionPropertyValue('oauth_token', 'test');
        $this->setReflectionPropertyValue('project_id', NULL);

        $this->logger->expects('warning')
                     ->once()
                     ->with('Tried to push FCM notification but project id is not provided.');

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                        [ 'endpoint' => 'endpoint', 'error' => 'Invalid argument' ]
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                        [ 'endpoint' => 'endpoint1', 'error' => 'Invalid argument' ]
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                        [ 'endpoint' => 'endpoint2', 'error' => 'Invalid argument' ]
                     );

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\FCM\FCMResponse', $result);
    }

    /**
     * Test that push() resets the properties after a push.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushResetsProperties(): void
    {
        $endpoints = [ 'endpoint' ];

        $this->payload->expects('is_broadcast')
                      ->times(2)
                      ->andReturn(FALSE);

        $this->payload->expects('set_token')
                      ->once()
                      ->with('endpoint')
                      ->andReturnSelf();

        $this->payload->expects('get_json_payload')
                      ->once()
                      ->andReturn('{"collapse_key":"abcde-12345"}');

        $this->setReflectionPropertyValue('oauth_token', 'oauth_token');
        $this->setReflectionPropertyValue('project_id', 'fcm-project');

        $response = $this->getMockBuilder(Response::class)->getMock();

        $this->http->expects($this->once())
                   ->method('post')
                   ->willReturn($response);

        $this->logger->expects('warning')
                     ->once()
                     ->withAnyArgs();

        $this->class->push($this->payload, $endpoints);

        $this->assertPropertyEquals('oauth_token', 'oauth_token');
        $this->assertPropertyEquals('project_id', 'fcm-project');
    }

    /**
     * Test that push() works as expected when the request failed.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushWithFailedRequest(): void
    {
        $this->setReflectionPropertyValue('oauth_token', 'oauth_token');
        $this->setReflectionPropertyValue('project_id', 'fcm-project');

        $this->mockFunction('curl_errno', function () { return 10; });

        $endpoints = [ 'endpoint' ];

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer oauth_token',
        ];

        $url = 'https://fcm.googleapis.com/v1/projects/fcm-project/messages:send';

        $options = [
            'timeout'          => 30,
            'connect_timeout'  => 30,
            'protocol_version' => 2.0,
        ];

        $this->payload->expects('is_broadcast')
                      ->times(2)
                      ->andReturn(FALSE);

        $this->payload->expects('set_token')
                      ->once()
                      ->with('endpoint')
                      ->andReturnSelf();

        $this->payload->expects('get_json_payload')
                      ->once()
                      ->andReturn('{"token":"endpoint"}');

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($url, $headers, '{"token":"endpoint"}', $options)
                   ->willThrowException(new RequestsException('cURL error 10: Request error', 'curlerror', NULL));

        $context = [ 'endpoint' => 'endpoint', 'error' => 'cURL error 10: Request error' ];

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}', $context);

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\FCM\FCMResponse', $result);

        $this->assertSame($result->get_status('endpoint'), PushNotificationStatus::TemporaryError);

        $this->unmockFunction('curl_errno');
    }

    /**
     * Test that push() works as expected when the request failed.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushWithTimeoutRequest()
    {
        $this->setReflectionPropertyValue('oauth_token', 'oauth_token');
        $this->setReflectionPropertyValue('project_id', 'fcm-project');

        $this->mockFunction('curl_errno', function () { return 28; });

        $endpoints = [ 'endpoint' ];

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer oauth_token',
        ];

        $url = 'https://fcm.googleapis.com/v1/projects/fcm-project/messages:send';

        $options = [
            'timeout'          => 30,
            'connect_timeout'  => 30,
            'protocol_version' => 2.0,
        ];

        $this->payload->expects('is_broadcast')
                      ->times(2)
                      ->andReturn(FALSE);

        $this->payload->expects('set_token')
                      ->once()
                      ->with('endpoint')
                      ->andReturnSelf();

        $this->payload->expects('get_json_payload')
                      ->once()
                      ->andReturn('{"token":"endpoint"}');

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($url, $headers, '{"token":"endpoint"}', $options)
                   ->willThrowException(new RequestsException('cURL error 28: Request timed out', 'curlerror', NULL));

        $context = [ 'endpoint' => 'endpoint', 'error' => 'cURL error 28: Request timed out' ];

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}', $context);

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\FCM\FCMResponse', $result);

        $this->assertSame($result->get_status('endpoint'), PushNotificationStatus::TemporaryError);

        $this->unmockFunction('curl_errno');
    }

    /**
     * Test that push() sends correct request with no properties set except the endpoint.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushRequestWithDefaultValues(): void
    {
        $this->setReflectionPropertyValue('oauth_token', 'oauth_token');
        $this->setReflectionPropertyValue('project_id', 'fcm-project');

        $endpoints = [ 'endpoint' ];

        $response = $this->getMockBuilder(Response::class)->getMock();

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer oauth_token',
        ];

        $url  = 'https://fcm.googleapis.com/v1/projects/fcm-project/messages:send';
        $post = '{"token":"endpoint"}';

        $options = [
            'timeout'          => 30,
            'connect_timeout'  => 30,
            'protocol_version' => 2.0,
        ];

        $this->payload->expects('is_broadcast')
                      ->times(2)
                      ->andReturn(FALSE);

        $this->payload->expects('set_token')
                      ->once()
                      ->with('endpoint')
                      ->andReturnSelf();

        $this->payload->expects('get_json_payload')
                      ->once()
                      ->andReturn($post);

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($url, $headers, $post, $options)
                   ->willReturn($response);

        $this->logger->expects('warning')
                     ->once()
                     ->withAnyArgs();

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that push() sends correct request with single endpoint.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushRequestWithSingleEndpoint(): void
    {
        $this->setReflectionPropertyValue('oauth_token', 'oauth_token');
        $this->setReflectionPropertyValue('project_id', 'fcm-project');

        $endpoints = [ 'endpoint' ];

        $response = $this->getMockBuilder(Response::class)->getMock();

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer oauth_token',
        ];

        $url  = 'https://fcm.googleapis.com/v1/projects/fcm-project/messages:send';
        $post = '{"collapse_key":"abcde-12345","token":"endpoint"}';

        $options = [
            'timeout'          => 30,
            'connect_timeout'  => 30,
            'protocol_version' => 2.0,
        ];

        $this->payload->expects('is_broadcast')
                      ->times(2)
                      ->andReturn(FALSE);

        $this->payload->expects('set_token')
                      ->once()
                      ->with('endpoint')
                      ->andReturnSelf();

        $this->payload->expects('get_json_payload')
                      ->once()
                      ->andReturn($post);

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($url, $headers, $post, $options)
                   ->willReturn($response);

        $this->logger->expects('warning')
                     ->once()
                     ->withAnyArgs();

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that push() generates a payload that doesn't encode unicode characters.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushRequestWithMultibyteCharacters(): void
    {
        $this->setReflectionPropertyValue('oauth_token', 'oauth_token');
        $this->setReflectionPropertyValue('project_id', 'fcm-project');

        $endpoints = [ 'endpoint' ];

        $response = $this->getMockBuilder(Response::class)->getMock();

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer oauth_token',
        ];

        $url  = 'https://fcm.googleapis.com/v1/projects/fcm-project/messages:send';
        $post = '{"collapse_key":"abcde-12345","data":{"message":"凄い"},"token":"endpoint"}';

        $options = [
            'timeout'          => 30,
            'connect_timeout'  => 30,
            'protocol_version' => 2.0,
        ];

        $this->payload->expects('is_broadcast')
                      ->times(2)
                      ->andReturn(FALSE);

        $this->payload->expects('set_token')
                      ->once()
                      ->with('endpoint')
                      ->andReturnSelf();

        $this->payload->expects('get_json_payload')
                      ->once()
                      ->andReturn($post);

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($url, $headers, $post, $options)
                   ->willReturn($response);

        $this->logger->expects('warning')
                     ->once()
                     ->withAnyArgs();

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that push() sends correct request with multiple endpoints.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushRequestWithMultipleEndpoints(): void
    {
        $this->setReflectionPropertyValue('oauth_token', 'oauth_token');
        $this->setReflectionPropertyValue('project_id', 'fcm-project');

        $this->mockFunction('curl_errno', function () { return 28; });

        $endpoints = [ 'endpoint', 'endpoint1', 'endpoint2', 'endpoint3' ];

        $response_200 = $this->getMockBuilder(Response::class)->getMock();
        $response_403 = $this->getMockBuilder(Response::class)->getMock();

        $response_200->status_code = 200;
        $response_403->status_code = 403;

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer oauth_token',
        ];

        $url  = 'https://fcm.googleapis.com/v1/projects/fcm-project/messages:send';
        $post = '{"collapse_key":"abcde-12345","token":"endpoint"}';

        $options = [
            'timeout'          => 30,
            'connect_timeout'  => 30,
            'protocol_version' => 2.0,
        ];

        $responses = [
            'endpoint'  => $response_200,
            'endpoint1' => new RequestsException('cURL error 28: Request timed out', 'curlerror', NULL),
            'endpoint2' => $response_403,
            'endpoint3' => new RequestsException('cURL error 28: Request timed out', 'curlerror', NULL),
        ];

        $this->payload->expects('is_broadcast')
                      ->times(2)
                      ->andReturn(FALSE);

        $this->payload->expects('set_token')
                      ->once()
                      ->with('endpoint')
                      ->andReturnSelf();

        $this->payload->expects('set_token')
                      ->once()
                      ->with('endpoint1')
                      ->andReturnSelf();

        $this->payload->expects('set_token')
                      ->once()
                      ->with('endpoint2')
                      ->andReturnSelf();

        $this->payload->expects('set_token')
                      ->once()
                      ->with('endpoint3')
                      ->andReturnSelf();

        $this->payload->expects('get_json_payload')
                      ->times(4)
                      ->andReturn($post);

        $this->http->expects($this->exactly(4))
                   ->method('post')
                   ->with($url, $headers, $post, $options)
                   ->willReturnOnConsecutiveCalls(
                       $responses['endpoint'],
                       $responses['endpoint1'],
                       $responses['endpoint2'],
                       $responses['endpoint3'],
                   );

        $this->logger->expects('warning')
                     ->times(3)
                     ->withAnyArgs();

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertSame($result->get_status('endpoint'), PushNotificationStatus::Success);
        $this->assertSame($result->get_status('endpoint1'), PushNotificationStatus::TemporaryError);
        $this->assertSame($result->get_status('endpoint2'), PushNotificationStatus::InvalidEndpoint);
        $this->assertSame($result->get_status('endpoint3'), PushNotificationStatus::TemporaryError);

        $this->unmockFunction('curl_errno');
    }

    /**
     * Test that push() sends correct request with topic and no endpoints.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushRequestWithNoEndpointsToTopic(): void
    {
        $this->setReflectionPropertyValue('oauth_token', 'oauth_token');
        $this->setReflectionPropertyValue('project_id', 'fcm-project');

        $endpoints = [];

        $response_200 = $this->getMockBuilder(Response::class)->getMock();

        $response_200->status_code = 200;

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer oauth_token',
        ];

        $url  = 'https://fcm.googleapis.com/v1/projects/fcm-project/messages:send';
        $post = '{"collapse_key":"abcde-12345","topic":"topic"}';

        $options = [
            'timeout'          => 30,
            'connect_timeout'  => 30,
            'protocol_version' => 2.0,
        ];

        $this->payload->expects('is_broadcast')
                      ->times(3)
                      ->andReturn(TRUE);

        $this->payload->expects('set_token')->never();

        $this->payload->expects('get_json_payload')
                      ->once()
                      ->andReturn($post);

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($url, $headers, $post, $options)
                   ->willReturn($response_200);

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that push() sends correct request with topic and multiple endpoints.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushRequestWithMultipleEndpointsToTopic(): void
    {
        $this->setReflectionPropertyValue('oauth_token', 'oauth_token');
        $this->setReflectionPropertyValue('project_id', 'fcm-project');

        $endpoints = [ 'endpoint', 'endpoint1', 'endpoint2', 'endpoint3' ];

        $response_200 = $this->getMockBuilder(Response::class)->getMock();

        $response_200->status_code = 200;

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer oauth_token',
        ];

        $url  = 'https://fcm.googleapis.com/v1/projects/fcm-project/messages:send';
        $post = '{"collapse_key":"abcde-12345","topic":"topic"}';

        $options = [
            'timeout'          => 30,
            'connect_timeout'  => 30,
            'protocol_version' => 2.0,
        ];

        $this->payload->expects('is_broadcast')
                      ->twice()
                      ->andReturn(TRUE);

        $this->payload->expects('set_token')->never();

        $this->payload->expects('get_json_payload')
                      ->once()
                      ->andReturn($post);

        $this->logger->expects('warning')
                     ->times(4)
                     ->withAnyArgs();

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($url, $headers, $post, $options)
                   ->willReturn($response_200);

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that push() sends correct request with condition and no endpoints.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushRequestWithNoEndpointsToCondition(): void
    {
        $this->setReflectionPropertyValue('oauth_token', 'oauth_token');
        $this->setReflectionPropertyValue('project_id', 'fcm-project');

        $endpoints = [];

        $response_200 = $this->getMockBuilder(Response::class)->getMock();

        $response_200->status_code = 200;

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer oauth_token',
        ];

        $url  = 'https://fcm.googleapis.com/v1/projects/fcm-project/messages:send';
        $post = '{"collapse_key":"abcde-12345","condition":"condition"}';

        $options = [
            'timeout'          => 30,
            'connect_timeout'  => 30,
            'protocol_version' => 2.0,
        ];

        $this->payload->expects('is_broadcast')
                      ->times(3)
                      ->andReturn(TRUE);

        $this->payload->expects('set_token')
                      ->never();

        $this->payload->expects('get_json_payload')
                      ->once()
                      ->andReturn($post);

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($url, $headers, $post, $options)
                   ->willReturn($response_200);

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that push() sends correct request with condition and multiple endpoints.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushRequestWithMultipleEndpointsToCondition(): void
    {
        $this->setReflectionPropertyValue('oauth_token', 'oauth_token');
        $this->setReflectionPropertyValue('project_id', 'fcm-project');

        $endpoints = [ 'endpoint', 'endpoint1', 'endpoint2', 'endpoint3' ];

        $response_200 = $this->getMockBuilder(Response::class)->getMock();

        $response_200->status_code = 200;

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer oauth_token',
        ];

        $url  = 'https://fcm.googleapis.com/v1/projects/fcm-project/messages:send';
        $post = '{"collapse_key":"abcde-12345","topic":"topic"}';

        $options = [
            'timeout'          => 30,
            'connect_timeout'  => 30,
            'protocol_version' => 2.0,
        ];

        $this->payload->expects('is_broadcast')
                      ->twice()
                      ->andReturn(TRUE);

        $this->payload->expects('set_token')
                      ->never();

        $this->payload->expects('get_json_payload')
                      ->once()
                      ->andReturn($post);

        $this->logger->expects('warning')
                     ->times(4)
                     ->withAnyArgs();

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($url, $headers, $post, $options)
                   ->willReturn($response_200);

        $this->class->push($this->payload, $endpoints);
    }

}

?>
