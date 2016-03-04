<?php
/*
 * @author Tobias Olry <tobias.olry@gmail.com>
 */

namespace DavidBadura\SimpleCsv\Tests;

use DavidBadura\SimpleCsv\CsvParser;

class ByteOrderMarkTest extends \PHPUnit_Framework_TestCase
{
    public static function testBomProvider()
    {
        return array(
            array(__DIR__ . '/_files/byte-order-mark-test.csv'),
            array(__DIR__ . '/_files/byte-order-mark-test-with-enclosure.csv'),
        );
    }

    /**
     * @dataProvider testBomProvider
     */
    public function testBom($file)
    {
        foreach (new CsvParser($file, ';') as $row) {
            $this->assertEquals($row['col-with-bom'], 'data-col-with-bom');
        }
    }
}
