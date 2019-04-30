<?php

declare(strict_types=1);

namespace ViewerTest\View\Twig;

use Twig\TwigFilter;
use PHPUnit\Framework\TestCase;
use Viewer\View\Twig\OrdinalNumberExtension;
use InvalidArgumentException;

class OrdinalNumberExtensionTest extends TestCase
{
    public function testGetFilters()
    {
        $extension = new OrdinalNumberExtension();

        $filters = $extension->getFilters();

        $this->assertTrue(is_array($filters));
        $this->assertEquals(1, count($filters));

        $filter = array_pop($filters);

        $this->assertInstanceOf(TwigFilter::class, $filter);
        $this->assertEquals('ordinal', $filter->getName());

        $filterCallable = $filter->getCallable();
        $this->assertInstanceOf(OrdinalNumberExtension::class, $filterCallable[0]);
        $this->assertEquals('makeOrdinal', $filterCallable[1]);
    }

    /**
     * @dataProvider exceptionOrdinalDataProvider
     */
    public function testMakeOrdinalException($value)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ordinals can only be provided for integers');

        $extension = new OrdinalNumberExtension();

        $extension->makeOrdinal($value);
    }

    public function exceptionOrdinalDataProvider()
    {
        return [
            [
                'abc',
                null,
                true,
                false,
                new \stdClass(),
                1.2345
            ],
        ];
    }

    /**
     * @dataProvider ordinalDataProvider
     */
    public function testMakeOrdinal($value, $expected)
    {
        $extension = new OrdinalNumberExtension();

        $ordinal = $extension->makeOrdinal($value);

        $this->assertEquals($expected, $ordinal);
    }

    public function ordinalDataProvider()
    {
        return [
            [1, '1st'],
            [2, '2nd'],
            [3, '3rd'],
            [4, '4th'],
            [5, '5th'],
            [6, '6th'],
            [7, '7th'],
            [8, '8th'],
            [9, '9th'],
            [10, '10th'],
            [11, '11th'],
            [12, '12th'],
            [13, '13th'],
            [14, '14th'],
            [15, '15th'],
            [16, '16th'],
            [17, '17th'],
            [18, '18th'],
            [19, '19th'],
            [20, '20th'],
            [21, '21st'],
            [22, '22nd'],
            [23, '23rd'],
            [24, '24th'],
            [25, '25th'],
            [26, '26th'],
            [27, '27th'],
            [28, '28th'],
            [29, '29th'],
            [30, '30th'],
            [31, '31st'],
            [32, '32nd'],
            [33, '33rd'],
            [34, '34th'],
            [35, '35th'],
            [36, '36th'],
            [37, '37th'],
            [38, '38th'],
            [39, '39th'],
            [40, '40th'],
            [41, '41st'],
            [42, '42nd'],
            [43, '43rd'],
            [44, '44th'],
            [45, '45th'],
            [46, '46th'],
            [47, '47th'],
            [48, '48th'],
            [49, '49th'],
            [50, '50th'],
            [51, '51st'],
            [52, '52nd'],
            [53, '53rd'],
            [54, '54th'],
            [55, '55th'],
            [56, '56th'],
            [57, '57th'],
            [58, '58th'],
            [59, '59th'],
            [60, '60th'],
            [61, '61st'],
            [62, '62nd'],
            [63, '63rd'],
            [64, '64th'],
            [65, '65th'],
            [66, '66th'],
            [67, '67th'],
            [68, '68th'],
            [69, '69th'],
            [70, '70th'],
            [71, '71st'],
            [72, '72nd'],
            [73, '73rd'],
            [74, '74th'],
            [75, '75th'],
            [76, '76th'],
            [77, '77th'],
            [78, '78th'],
            [79, '79th'],
            [80, '80th'],
            [81, '81st'],
            [82, '82nd'],
            [83, '83rd'],
            [84, '84th'],
            [85, '85th'],
            [86, '86th'],
            [87, '87th'],
            [88, '88th'],
            [89, '89th'],
            [90, '90th'],
            [91, '91st'],
            [92, '92nd'],
            [93, '93rd'],
            [94, '94th'],
            [95, '95th'],
            [96, '96th'],
            [97, '97th'],
            [98, '98th'],
            [99, '99th'],
            [100, '100th'],
            [101, '101st'],
            [102, '102nd'],
            [103, '103rd'],
            [104, '104th'],
            [105, '105th'],
            [106, '106th'],
            [107, '107th'],
            [108, '108th'],
            [109, '109th'],
            [110, '110th'],
        ];
    }
}
