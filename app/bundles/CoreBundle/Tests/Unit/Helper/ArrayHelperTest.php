<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Tests\Unit\Helper;

use Mautic\CoreBundle\Helper\ArrayHelper;

class ArrayHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testGetValue()
    {
        $origin = ['one', 'two' => 'three'];

        $this->assertSame('one', ArrayHelper::getValue(0, $origin));
        $this->assertSame('three', ArrayHelper::getValue('two', $origin));
        $this->assertNull(ArrayHelper::getValue('five', $origin));
        $this->assertSame('default', ArrayHelper::getValue('five', $origin, 'default'));
    }

    public function testPickValue()
    {
        $origin = ['one', 'two' => 'three', 'four' => null];

        $this->assertSame('one', ArrayHelper::pickValue(0, $origin));
        $this->assertSame(['two' => 'three', 'four' => null], $origin);
        $this->assertSame('three', ArrayHelper::pickValue('two', $origin));
        $this->assertSame(['four' => null], $origin);
        $this->assertNull(ArrayHelper::pickValue('five', $origin));
        $this->assertSame('default', ArrayHelper::pickValue('five', $origin, 'default'));
        $this->assertNull(ArrayHelper::pickValue('four', $origin, 'default'));
        $this->assertSame([], $origin);
    }

    public function testSelect()
    {
        $origin = ['one', 'two' => 'three', 'four' => 'five'];

        $this->assertSame(['two' => 'three'], ArrayHelper::select(['two'], $origin));
        $this->assertSame(['two' => 'three', 'four' => 'five'], ArrayHelper::select(['two', 'four'], $origin));
        $this->assertSame(['one', 'two' => 'three'], ArrayHelper::select(['two', 0], $origin));
    }

    public function testflipArray()
    {
        $array = [
            'first' => 'Custom first',
            'second'=> 'Custom second',
        ];

        $this->assertSame(array_flip($array), ArrayHelper::flipArray($array));

        $array = [
            'group1' => [
                'first' => 'Custom first',
            ],
            'group2' => [
                'second' => 'Custom second',
            ],
        ];

        $flippedArray = ArrayHelper::flipArray($array);

        $this->assertEquals('Custom first', key($flippedArray['group1']));
        $this->assertEquals('first', end($flippedArray['group1']));
    }
}
