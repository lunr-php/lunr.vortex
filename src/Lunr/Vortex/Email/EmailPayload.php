<?php

/**
 * This file contains functionality to generate Email Notification payloads.
 *
 * SPDX-FileCopyrightText: Copyright 2014 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\Email;

use Lunr\Vortex\PushNotificationPayloadInterface;

/**
 * Email Notification Payload Generator.
 *
 * @phpstan-type MailPayload array{
 *     charset: string,
 *     encoding: "8bit"|"7bit"|"binary"|"base64"|"quoted-printable",
 *     subject: string,
 *     body: string,
 *     body_as_html: bool
 * }
 */
class EmailPayload implements PushNotificationPayloadInterface
{

    /**
     * Array of Email Notification message elements.
     * @var MailPayload
     */
    protected array $elements;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->elements = [
            'charset'      => 'UTF-8',
            'encoding'     => 'base64',
            'subject'      => '',
            'body'         => '',
            'body_as_html' => FALSE,
        ];
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->elements);
    }

    /**
     * Check if the payload is for a broadcast notification.
     *
     * @return bool If payload for notification is a broadcast
     */
    public function is_broadcast(): bool
    {
        return FALSE;
    }

    /**
     * Construct the payload for the email notification.
     *
     * @return MailPayload The Email Payload
     */
    public function get_payload(): array
    {
        return $this->elements;
    }

    /**
     * Sets the email body of the payload.
     *
     * @param string $body The body of the email
     *
     * @return EmailPayload Self Reference
     */
    public function set_body(string $body): self
    {
        $this->elements['body'] = $body;

        return $this;
    }

    /**
     * Sets the email body of the payload.
     *
     * @param string $subject The subject of the email
     *
     * @return EmailPayload Self Reference
     */
    public function set_subject(string $subject): self
    {
        $this->elements['subject'] = $subject;

        return $this;
    }

    /**
     * Sets the email character set of the payload.
     *
     * Default character set is UTF-8.
     *
     * @param string $charset The character set of the email
     *
     * @return EmailPayload Self Reference
     */
    public function set_charset(string $charset): self
    {
        $this->elements['charset'] = $charset;

        return $this;
    }

    /**
     * Sets the email encoding of the payload.
     *
     * Default encoding is base64.
     *
     * @param "8bit"|"7bit"|"binary"|"base64"|"quoted-printable" $encoding The encoding of the email
     *
     * @return EmailPayload Self Reference
     */
    public function set_encoding(string $encoding): self
    {
        $this->elements['encoding'] = $encoding;

        return $this;
    }

    /**
     * Configure if the body should be treated as HTML.
     *
     * @param bool $as_html If the body should be treated as HTML
     *
     * @return self Self reference
     */
    public function body_as_html(bool $as_html): self
    {
        $this->elements['body_as_html'] = $as_html;

        return $this;
    }

}

?>
