<?php

/**
 * This file contains the FCMDispatcherBaseTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

/**
 * This class contains test for the constructor of the FCMDispatcher class.
 *
 * @covers Lunr\Vortex\FCM\FCMDispatcher
 */
class FCMDispatcherBaseTest extends FCMDispatcherTest
{

    /**
     * Test get_new_response_object_for_failed_request().
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::get_new_response_object_for_failed_request
     */
    public function testGetNewResponseObjectForFailedRequest(): void
    {
        $this->set_reflection_property_value('project_id', 'fcm-project');

        $method = $this->get_accessible_reflection_method('get_new_response_object_for_failed_request');

        $result = $method->invoke($this->class);

        $this->assertInstanceOf('WpOrg\Requests\Response', $result);
        $this->assertEquals('https://fcm.googleapis.com/v1/projects/fcm-project/messages:send', $result->url);
    }

    /**
     * Test that get_response() returns FCMResponseObject.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::get_response
     */
    public function testGetResponseReturnsFCMResponseObject(): void
    {
        $result = $this->class->get_response($this->response, $this->logger, 'endpoint', '{}');

        $this->assertInstanceOf('Lunr\Vortex\FCM\FCMResponse', $result);
    }

}

?>
