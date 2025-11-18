<?php

/**
 * This file contains the JPushBatchResponseGetMessageIdTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2023 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush\Tests;

use Lunr\Vortex\JPush\JPushBatchResponse;

/**
 * This class contains tests for the JPushBatchResponse class.
 *
 * @covers \Lunr\Vortex\JPush\JPushBatchResponse
 */
class JPushBatchResponseGetMessageIdTest extends JPushBatchResponseTestCase
{

    /**
     * Test get_message_id returns NULL when batch failed.
     *
     * @covers \Lunr\Vortex\JPush\JPushBatchResponse::get_message_id
     */
    public function testGetMessageIdWhenBatchFails(): void
    {
        $httpCode = 400;
        $content  = '{"error": {"message": "Field \"collapse_key\" must be a JSON string: 1463565451"}}';

        $this->response->status_code = $httpCode;
        $this->response->body        = $content;

        $this->class = new JPushBatchResponse($this->http, $this->logger, $this->response, [], '[]');

        $this->assertNull($this->class->get_message_id());
    }

    /**
     * Test get_message_id returns message id when batch succeeds.
     *
     * @covers \Lunr\Vortex\JPush\JPushBatchResponse::__construct
     */
    public function testGetMessageIdWhenBatchSucceeds(): void
    {
        $content = [ 'msg_id' => '121654513215' ];

        $this->response->success = TRUE;
        $this->response->body    = json_encode($content);

        $this->class = new JPushBatchResponse($this->http, $this->logger, $this->response, [], '[]');

        $this->assertSame(121654513215, $this->class->get_message_id());
    }

}

?>
