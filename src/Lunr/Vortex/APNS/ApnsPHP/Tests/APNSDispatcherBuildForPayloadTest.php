<?php

/**
 * This file contains the APNSDispatcherBuildForPayloadTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS\ApnsPHP\Tests;

use ApnsPHP\Message;
use ApnsPHP\Message\LiveActivity;
use ApnsPHP\Message\LiveActivityEvent;
use ApnsPHP\Message\Priority;

/**
 * This class contains tests for the build_*_for_payload() method of the APNSDispatcher class.
 *
 * @covers Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher
 */
class APNSDispatcherBuildForPayloadTest extends APNSDispatcherTest
{

    /**
     * Test that build_message_for_payload() constructs the correct full payload.
     *
     * @covers \Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher::build_message_for_payload
     */
    public function testBuildMessageForPayloadConstructsCorrectPayload(): void
    {
        $payload = [
            'title'             => 'title',
            'body'              => 'message',
            'thread_id'         => '59ADAE4572BF42A682F46170DA5A74EC',
            'sound'             => 'yo.mp3',
            'category'          => 'messages_for_test',
            'mutable_content'   => TRUE,
            'content_available' => TRUE,
            'topic'             => 'com.company.app',
            'priority'          => Priority::ConsiderPowerUsage,
            'collapse_key'      => 'key',
            'identifier'        => 'DF9AEF66-F39A-48C1-A5A8-69D263E21F1C',
            'yo'                => 'he',
            'badge'             => 7,
            'custom_data'       => [
                'key1' => 'value1',
                'key2' => 'value2'
            ],
        ];

        $message = new Message();
        $message->setTitle($payload['title']);
        $message->setText($payload['body']);
        $message->setThreadId($payload['thread_id']);
        $message->setSound($payload['sound']);
        $message->setCategory($payload['category']);
        $message->setMutableContent($payload['mutable_content']);
        $message->setContentAvailable($payload['content_available']);
        $message->setTopic($payload['topic']);
        $message->setPriority($payload['priority']);
        $message->setCollapseId($payload['collapse_key']);
        $message->setCustomIdentifier($payload['identifier']);
        $message->setBadge($payload['badge']);
        foreach ($payload['custom_data'] as $key => $value)
        {
            $message->setCustomProperty($key, $value);
        }

        $this->alert_payload->expects($this->once())
                            ->method('get_payload')
                            ->willReturn($payload);

        $return = $this->get_reflection_method('build_message_for_payload')
                       ->invokeArgs($this->class, [ $this->alert_payload ]);

        $this->assertEquals($message, $return);
    }

    /**
     * Test that build_live_activity_for_payload() constructs the correct full payload.
     *
     * @covers \Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher::build_live_activity_for_payload
     */
    public function testBuildLiveActivityForPayloadConstructsCorrectPayload(): void
    {
        $payload = [
            'title'             => 'title',
            'body'              => 'message',
            'thread_id'         => '59ADAE4572BF42A682F46170DA5A74EC',
            'sound'             => 'yo.mp3',
            'category'          => 'messages_for_test',
            'mutable_content'   => TRUE,
            'content_available' => TRUE,
            'topic'             => 'com.company.app.push-type.liveactivity',
            'priority'          => Priority::ConsiderPowerUsage,
            'collapse_key'      => 'key',
            'identifier'        => 'DF9AEF66-F39A-48C1-A5A8-69D263E21F1C',
            'yo'                => 'he',
            'badge'             => 7,
            'event'             => LiveActivityEvent::Start,
            'attributesType'    => 'SomeType',
            'staleTime'         => 1732285170,
            'dismissTime'       => 1732285170,
            'attributes'        => [
                'key1' => 'value1',
                'key2' => 'value2'
            ],
            'contentState'      => [
                'key1' => 'value1',
                'key2' => 'value2'
            ],
            'custom_data'       => [
                'key1' => 'value1',
                'key2' => 'value2'
            ],
        ];

        $message = new LiveActivity();
        $message->setTitle($payload['title']);
        $message->setText($payload['body']);
        $message->setThreadId($payload['thread_id']);
        $message->setSound($payload['sound']);
        $message->setCategory($payload['category']);
        $message->setMutableContent($payload['mutable_content']);
        $message->setContentAvailable($payload['content_available']);
        $message->setTopic($payload['topic']);
        $message->setPriority($payload['priority']);
        $message->setCollapseId($payload['collapse_key']);
        $message->setCustomIdentifier($payload['identifier']);
        $message->setBadge($payload['badge']);
        $message->setEvent($payload['event']);
        $message->setAttributesType($payload['attributesType']);
        $message->setAttributes($payload['attributes']);
        $message->setContentState($payload['contentState']);
        $message->setStaleTimestamp($payload['staleTime']);
        $message->setDismissTimestamp($payload['dismissTime']);
        foreach ($payload['custom_data'] as $key => $value)
        {
            $message->setCustomProperty($key, $value);
        }

        $this->la_payload->expects($this->exactly(2))
                         ->method('get_payload')
                         ->willReturn($payload);

        $return = $this->get_reflection_method('build_live_activity_for_payload')
                       ->invokeArgs($this->class, [ $this->la_payload ]);

        $this->assertEquals($message, $return);
    }

}

?>
