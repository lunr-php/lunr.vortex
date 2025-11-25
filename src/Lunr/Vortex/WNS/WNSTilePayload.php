<?php

/**
 * This file contains functionality to generate Windows Tile Push Notification payloads.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\WNS;

/**
 * Windows Tile Push Notification Payload Generator.
 *
 * @phpstan-type WNSTilePayloadElements array{
 *     image: string[],
 *     templates: string[],
 *     text: string[]
 * }
 */
class WNSTilePayload extends WNSPayload
{

    /**
     * Array of Push Notification elements.
     * @var WNSTilePayloadElements
     */
    protected array $elements;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->elements = [
            'image'     => [],
            'templates' => [],
            'text'      => [],
        ];
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->elements);
        parent::__destruct();
    }

    /**
     * Construct the payload for the push notification.
     *
     * @return string Payload
     */
    public function get_payload(): string
    {
        $innerXML = '';
        foreach ($this->elements['image'] as $key => $value)
        {
            $innerXML .= '            <image id="' . ($key + 1) . '" src="' . $value . "\"/>\r\n";
        }

        foreach ($this->elements['text'] as $key => $value)
        {
            $innerXML .= '            <text id="' . ($key + 1) . '">' . $value . "</text>\r\n";
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";

        foreach ($this->elements['templates'] as $key => $template)
        {
            $xml .= '<tile>' . "\r\n";
            $xml .= '    <visual version="2">' . "\r\n";
            $xml .= '        <binding template="' . $template . '">' . "\r\n";
            $xml .= $innerXML;
            $xml .= '        </binding>' . "\r\n";
            $xml .= '    </visual>' . "\r\n";
            $xml .= '</tile>';
            $xml .= ($key < (count($this->elements['templates']) - 1)) ? "\r\n\r\n" : "\r\n";
        }

        return $xml;
    }

    /**
     * Set text for the tile notification.
     *
     * @param string[]|string $text Text on the tile
     *
     * @param int             $line The line on which to add the text
     *
     * @return self Self Reference
     */
    public function set_text(array|string $text, int $line = 0): self
    {
        if (!is_array($text))
        {
            $this->elements['text'][$line] = $this->escape_string($text);
            return $this;
        }

        foreach ($text as $key => $value)
        {
            $this->elements['text'][$key] = $this->escape_string($value);
        }

        return $this;
    }

    /**
     * Set image for the tile notification.
     *
     * @param string[]|string $image Image on the tile
     *
     * @param int             $line  The line on which to add the text
     *
     * @return self Self Reference
     */
    public function set_image(array|string $image, int $line = 0): self
    {
        if (!is_array($image))
        {
            $this->elements['image'][$line] = $this->escape_string($image);
            return $this;
        }

        foreach ($image as $key => $value)
        {
            $this->elements['image'][$key] = $this->escape_string($value);
        }

        return $this;
    }

    /**
     * Set template for the tile notification.
     *
     * @param string[]|string $templates Template(s) for notification
     *
     * @see https://msdn.microsoft.com/en-us/library/windows/apps/windows.ui.notifications.tiletemplatetype
     *
     * @return WNSTilePayload Self Reference
     */
    public function set_templates(array|string $templates): self
    {
        if (!is_array($templates))
        {
            $templates = [ $templates ];
        }

        foreach ($templates as $key => $template)
        {
            $this->elements['templates'][$key] = $this->escape_string($template);
        }

        return $this;
    }

}

?>
