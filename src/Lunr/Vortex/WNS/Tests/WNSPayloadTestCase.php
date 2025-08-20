<?php

/**
 * This file contains the WNSPayloadTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\WNS\Tests;

use Lunr\Halo\LunrBaseTestCase;
use Lunr\Vortex\WNS\WNSPayload;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the WNSPayload class.
 *
 * @covers Lunr\Vortex\WNS\WNSPayload
 */
abstract class WNSPayloadTestCase extends LunrBaseTestCase
{

    /**
     * Instance of the tested class.
     * @var WNSPayload&MockObject&Stub
     */
    protected WNSPayload&MockObject&Stub $class;

    /**
     * Testcase Constructor.
     */
    public function setUp(): void
    {
        $this->class = $this->getMockBuilder('Lunr\Vortex\WNS\WNSPayload')
                            ->getMockForAbstractClass();

        parent::baseSetUp($this->class);
    }

    /**
     * Testcase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->class);

        parent::tearDown();
    }

    /**
     * Unit test data provider for strings and their expected escaped counterparts.
     *
     * @return array $strings Array of strings
     */
    public static function stringProvider(): array
    {
        $strings   = [];
        $strings[] = [ 'string', 'string' ];
        $strings[] = [ '<string', '&lt;string' ];
        $strings[] = [ 'string>', 'string&gt;' ];
        $strings[] = [ '&string', '&amp;string' ];
        $strings[] = [ 'string‘s', 'string&apos;s' ];
        $strings[] = [ '“string“', '&quot;string&quot;' ];
        $strings[] = [ '<&“string‘s“>', '&lt;&amp;&quot;string&apos;s&quot;&gt;' ];

        return $strings;
    }

}

?>
