<?php

/**
 * This file contains the FCMTopicSubscribeTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use WpOrg\Requests\Exception as RequestsException;
use WpOrg\Requests\Exception\Http\Status400 as Http400Exception;

/**
 * This class contains tests for the constructor of the FCMTopic class.
 *
 * @covers Lunr\Vortex\FCM\FCMTopic
 */
class FCMTopicSubscribeTest extends FCMTopicTest
{

    /**
     * The default headers of the request.
     * @var array
     */
    private array $headers = [
        'Content-Type'      => 'application/json',
        'Authorization'     => 'Bearer oauth_token-abc123',
        'access_token_auth' => 'true',
    ];

    /**
     * The default options of the request.
     * @var array
     */
    private array $options = [
        'timeout'          => 30,
        'connect_timeout'  => 30,
        'protocol_version' => 2.0,
    ];

    /**
     * The default url of the request.
     * @var string
     */
    private string $url = 'https://iid.googleapis.com/iid/v1:batchAdd';

    /**
     * Test subscribe will throw an exception if the request was not successful
     *
     * @covers Lunr\Vortex\FCM\FCMTopic::subscribe
     */
    public function testSubscribeThrowsRequestException(): void
    {
        $this->expectException(RequestsException::class);
        $this->expectExceptionMessage('cURL error 10: Request error');

        $this->class->set_oauth_token('oauth_token-abc123');

        $body = '{"to":"/topics/test-topic","registration_tokens":["endpoint1"]}';

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($this->url, $this->headers, $body, $this->options)
                   ->willThrowException(new RequestsException('cURL error 10: Request error', 'curlerror', NULL));

        $this->response->expects($this->never())
                       ->method('throw_for_status');

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Subscribing FCM endpoints to topic {topic} failed: {message}',
                         [ 'topic' => 'test-topic', 'message' => 'cURL error 10: Request error' ]
                     );

        $this->class->subscribe('test-topic', [ 'endpoint1' ]);
    }

    /**
     * Test subscribe will throw an exception if the request was not successful
     *
     * @covers Lunr\Vortex\FCM\FCMTopic::subscribe
     */
    public function testSubscribeThrowsHttpException(): void
    {
        $this->expectException(Http400Exception::class);

        $this->class->set_oauth_token('oauth_token-abc123');

        $body = '{"to":"/topics/test-topic","registration_tokens":["endpoint1"]}';

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($this->url, $this->headers, $body, $this->options)
                   ->willReturn($this->response);

        $this->response->expects($this->once())
                       ->method('throw_for_status')
                       ->willThrowException(new Http400Exception());

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Subscribing FCM endpoints to topic {topic} failed: {message}',
                         [ 'topic' => 'test-topic', 'message' => '400 Bad Request' ]
                     );

        $this->class->subscribe('test-topic', [ 'endpoint1' ]);
    }

    /**
     * Test subscribe logs a warning if response is not json
     *
     * @covers Lunr\Vortex\FCM\FCMTopic::subscribe
     */
    public function testSubscribeFailsWhenResponseIsNotJson(): void
    {
        $this->class->set_oauth_token('oauth_token-abc123');

        $body = '{"to":"/topics/test-topic","registration_tokens":["endpoint1"]}';

        $this->response->body = '{';

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($this->url, $this->headers, $body, $this->options)
                   ->willReturn($this->response);

        $this->response->expects($this->once())
                       ->method('throw_for_status');

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Invalid response from FCM when subscribing FCM endpoints to topic {topic}: {message}',
                         [ 'topic' => 'test-topic', 'message' => '{' ]
                     );

        $this->class->subscribe('test-topic', [ 'endpoint1' ]);
    }

    /**
     * Test subscribe logs a warning if response is not an array
     *
     * @covers Lunr\Vortex\FCM\FCMTopic::subscribe
     */
    public function testSubscribeFailsWhenResponseIsNotAnArray(): void
    {
        $this->class->set_oauth_token('oauth_token-abc123');

        $body = '{"to":"/topics/test-topic","registration_tokens":["endpoint1"]}';

        $this->response->body = 'true';

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($this->url, $this->headers, $body, $this->options)
                   ->willReturn($this->response);

        $this->response->expects($this->once())
                       ->method('throw_for_status');

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Invalid response from FCM when subscribing FCM endpoints to topic {topic}: {message}',
                         [ 'topic' => 'test-topic', 'message' => 'true' ]
                     );

        $this->class->subscribe('test-topic', [ 'endpoint1' ]);
    }

    /**
     * Test subscribe logs a warning if response is an array with the results
     *
     * @covers Lunr\Vortex\FCM\FCMTopic::subscribe
     */
    public function testSubscribeFailsWhenResponseIsNotAnArrayWithTheResults(): void
    {
        $this->class->set_oauth_token('oauth_token-abc123');

        $body = '{"to":"/topics/test-topic","registration_tokens":["endpoint1"]}';

        $this->response->body = '{"test":[]}';

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($this->url, $this->headers, $body, $this->options)
                   ->willReturn($this->response);

        $this->response->expects($this->once())
                       ->method('throw_for_status');

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Invalid response from FCM when subscribing FCM endpoints to topic {topic}: {message}',
                         [ 'topic' => 'test-topic', 'message' => '{"test":[]}' ]
                     );

        $this->class->subscribe('test-topic', [ 'endpoint1' ]);
    }

    /**
     * Test subscribe logs a warning if subscribing an endpoint to the topic failed
     *
     * @covers Lunr\Vortex\FCM\FCMTopic::subscribe
     */
    public function testSubscribeFailsWhenSubscibingAnEndpointFails(): void
    {
        $this->class->set_oauth_token('oauth_token-abc123');

        $body = '{"to":"/topics/test-topic","registration_tokens":["endpoint1"]}';

        $this->response->body = '{"results":[{"error":"Not Found"}]}';

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($this->url, $this->headers, $body, $this->options)
                   ->willReturn($this->response);

        $this->response->expects($this->once())
                       ->method('throw_for_status');

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Subscribing FCM endpoint {endpoint} to topic {topic} failed: {message}',
                         [ 'endpoint' => 'endpoint1', 'topic' => 'test-topic', 'message' => 'Not Found' ]
                     );

        $this->class->subscribe('test-topic', [ 'endpoint1' ]);
    }

    /**
     * Test subscribe succeeds with one endpoint
     *
     * @covers Lunr\Vortex\FCM\FCMTopic::subscribe
     */
    public function testSubscribeSucceedsWithOneEndpoint(): void
    {
        $this->class->set_oauth_token('oauth_token-abc123');

        $body = '{"to":"/topics/test-topic","registration_tokens":["endpoint1"]}';

        $this->response->body = '{"results":[{}]}';

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($this->url, $this->headers, $body, $this->options)
                   ->willReturn($this->response);

        $this->response->expects($this->once())
                       ->method('throw_for_status');

        $this->logger->expects($this->never())
                     ->method('warning');

        $this->class->subscribe('test-topic', [ 'endpoint1' ]);
    }

    /**
     * Test subscribe succeeds with one endpoint
     *
     * @covers Lunr\Vortex\FCM\FCMTopic::subscribe
     */
    public function testSubscribeWithMultipleEndpoints(): void
    {
        $this->class->set_oauth_token('oauth_token-abc123');

        $body = '{"to":"/topics/test-topic","registration_tokens":["endpoint1","endpoint2","endpoint3"]}';

        $this->response->body = '{"results":[{},{"error":"Not Found"},{}]}';

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($this->url, $this->headers, $body, $this->options)
                   ->willReturn($this->response);

        $this->response->expects($this->once())
                       ->method('throw_for_status');

                       $this->logger->expects($this->once())
                       ->method('warning')
                       ->with(
                           'Subscribing FCM endpoint {endpoint} to topic {topic} failed: {message}',
                           [ 'endpoint' => 'endpoint2', 'topic' => 'test-topic', 'message' => 'Not Found' ]
                       );

        $this->class->subscribe('test-topic', [ 'endpoint1', 'endpoint2', 'endpoint3' ]);
    }

}

?>
