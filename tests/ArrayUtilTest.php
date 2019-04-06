<?php

namespace DanBallance\OasTools\Tests;

use DanBallance\OasTools\Utils\ArrayUtil;

/**
 * Class ArrayUtilTest
 *
 * @package DanBallance\OasTools\Tests
 * @author  Dan Ballance <work@danballance.uk>
 */
class ArrayUtilTest extends \PHPUnit\Framework\TestCase
{
    public function testArraySwap()
    {
        $anonClass = new class {
            use ArrayUtil;
            public function swap($array, $find, $replace)
            {
                return $this->arraySwap($array, $find, $replace);
            }
        };

        $this->assertEquals(
            [1, 2, 'c'],
            $anonClass->swap([1, 2, 3], 3, 'c')
        );
    }

    public function testArrayRemove()
    {
        $anonClass = new class {
            use ArrayUtil;
            public function remove($array, $find)
            {
                return $this->arrayRemove($array, $find);
            }
        };

        $this->assertEquals(
            [1, 2],
            $anonClass->remove([1, 2, 3], 3)
        );
    }
}
