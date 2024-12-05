<?php

/**
 * This file contains the EmailDispatcherPushTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2014 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\Email\Tests;

use Lunr\Vortex\APNS\APNSAlertPayload;
use Lunr\Vortex\FCM\FCMPayload;
use Lunr\Vortex\JPush\JPushMessagePayload;
use Lunr\Vortex\WNS\WNSTilePayload;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * This class contains test for the push() method of the EmailDispatcher class.
 *
 * @covers Lunr\Vortex\Email\EmailDispatcher
 */
class EmailDispatcherPushTest extends EmailDispatcherTest
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
        $data['fcm']   = [ new FCMPayload() ];
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
     * @covers       Lunr\Vortex\Email\EmailDispatcher::push
     */
    public function testPushingWithUnsupportedPayloadThrowsException($payload): void
    {
        $endpoints = [ 'endpoint' ];

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid payload object!');

        $this->class->push($payload, $endpoints);
    }

    /**
     * Test that push() returns EmailResponseObject.
     *
     * @covers Lunr\Vortex\Email\EmailDispatcher::push
     */
    public function testPushReturnsEmailResponseObject(): void
    {
        $endpoints = [ 'recipient@domain.com' ];

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn([
                          'subject'      => 'subject',
                          'body'         => 'body',
                          'charset'      => 'UTF-8',
                          'encoding'     => 'base64',
                          'body_as_html' => FALSE,
                      ]);

        $this->set_reflection_property_value('source', 'sender@domain.com');

        $this->mock_method([ $this->class, 'clone_mail' ], function () { return $this->mail_transport; }, 'private');

        $this->mail_transport->expects($this->once())
                             ->method('isHTML')
                             ->with(FALSE);

        $this->mail_transport->expects($this->once())
                             ->method('setFrom')
                             ->with($this->get_reflection_property_value('source'));

        $this->mail_transport->expects($this->once())
                             ->method('addAddress')
                             ->with('recipient@domain.com');

        $this->mail_transport->expects($this->once())
                             ->method('send')
                             ->will($this->returnValue(TRUE));

        $this->mail_transport->expects($this->once())
                             ->method('clearAddresses');

        $this->mail_transport->expects($this->once())
                             ->method('getSentMIMEMessage')
                             ->will($this->returnValue(''));

        $this->assertInstanceOf('Lunr\Vortex\Email\EmailResponse', $this->class->push($this->payload, $endpoints));

        $this->assertEquals($this->mail_transport->Subject, 'subject');
        $this->assertEquals($this->mail_transport->Body, 'body');
        $this->assertEquals($this->mail_transport->CharSet, 'UTF-8');
        $this->assertEquals($this->mail_transport->Encoding, 'base64');

        $this->unmock_method([ $this->class, 'clone_mail' ]);
    }

    /**
     * Test that push() returns EmailResponseObject also on error.
     *
     * @covers Lunr\Vortex\Email\EmailDispatcher::push
     */
    public function testPushReturnsEmailResponseObjectOnError(): void
    {
        $endpoints = [ 'recipient@domain.com' ];

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn([
                          'subject'      => 'subject',
                          'body'         => 'body',
                          'charset'      => 'UTF-8',
                          'encoding'     => 'base64',
                          'body_as_html' => FALSE,
                      ]);

        $this->set_reflection_property_value('source', 'sender@domain.com');

        $this->mock_method([ $this->class, 'clone_mail' ], function () { return $this->mail_transport; }, 'private');

        $this->mail_transport->expects($this->once())
                             ->method('isHTML')
                             ->with(FALSE);

        $this->mail_transport->expects($this->once())
                             ->method('setFrom')
                             ->with($this->get_reflection_property_value('source'));

        $this->mail_transport->expects($this->once())
                             ->method('addAddress')
                             ->with('recipient@domain.com');

        $this->mail_transport->expects($this->once())
                             ->method('send')
                             ->will($this->throwException(new PHPMailerException));

        $this->mail_transport->expects($this->once())
                             ->method('clearAddresses');

        $this->mail_transport->expects($this->once())
                             ->method('getSentMIMEMessage')
                             ->will($this->returnValue(''));

        $this->assertInstanceOf('Lunr\Vortex\Email\EmailResponse', $this->class->push($this->payload, $endpoints));

        $this->assertEquals($this->mail_transport->Subject, 'subject');
        $this->assertEquals($this->mail_transport->Body, 'body');
        $this->assertEquals($this->mail_transport->CharSet, 'UTF-8');
        $this->assertEquals($this->mail_transport->Encoding, 'base64');

        $this->unmock_method([ $this->class, 'clone_mail' ]);
    }

    /**
     * Test that push() resets the properties after a push.
     *
     * @covers Lunr\Vortex\Email\EmailDispatcher::push
     */
    public function testPushResetsProperties(): void
    {
        $endpoints = [ 'recipient@domain.com' ];

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn([
                          'subject'      => 'subject',
                          'body'         => 'body',
                          'charset'      => 'UTF-8',
                          'encoding'     => 'base64',
                          'body_as_html' => FALSE,
                      ]);

        $this->set_reflection_property_value('source', 'sender@domain.com');

        $this->mock_method([ $this->class, 'clone_mail' ], function () { return $this->mail_transport; }, 'private');

        $this->mail_transport->expects($this->once())
                             ->method('send')
                             ->will($this->returnValue(TRUE));

        $this->mail_transport->expects($this->once())
                             ->method('getSentMIMEMessage')
                             ->will($this->returnValue(''));

        $this->class->push($this->payload, $endpoints);

        $this->unmock_method([ $this->class, 'clone_mail' ]);
    }

}

?>
