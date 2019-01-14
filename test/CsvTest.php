<?php
declare(strict_types=1);
/**
 * File CsvTest.php
 *
 * @author Lukas Werner <kontakt@lwerner.de>
 * @since 13.01.2019
 */

namespace PhpCsvTest;

use PhpCsv\Csv;
use PHPUnit\Framework\TestCase;
use \SplFileInfo;

/**
 * Class CsvTest
 *
 * @package PhpCsvTest
 * @covers \PhpCsv\Csv
 * @uses SplFileInfo
 */
class CsvTest extends TestCase
{

    /**
     * @var array
     */
    private $lineBuffer = [];

    /**
     * @var array
     */
    private $chunkBuffer = [];

    /**
     * Tests if FileNotFoundException will be thrown
     *
     * @expectedException \PhpCsv\Exception\FileNotFoundException
     * @expectedExceptionMessage File "/files/file_03.csv" not found.
     */
    public function testFileNotFound(): void
    {
        $fileInfo = new SplFileInfo('/files/file_03.csv');
        $csv = new Csv($fileInfo);
        /** @noinspection PhpUnhandledExceptionInspection */
        $csv->count();
    }

    /**
     * Tests if readLine() throws an exception if column counts don't match
     *
     * @expectedException \PhpCsv\Exception\ReadLineException
     * @expectedExceptionMessage Header field number and row field number do not match at line 3.
     */
    public function testNotMatchingColumnCounts(): void
    {
        $fileInfo = new SplFileInfo(__DIR__ . '/files/file_05.csv');
        $csv = new Csv($fileInfo);
        /** @noinspection PhpUnhandledExceptionInspection */
        $csv->count();
    }

    /**
     * Tests count() method
     */
    public function testCount(): void
    {
        $fileInfo = new SplFileInfo(__DIR__ . '/files/file_01.csv');
        $csv = new Csv($fileInfo);
        /** @noinspection PhpUnhandledExceptionInspection */
        $count = $csv->count();
        $this->assertSame(5, $count);
        $this->assertTrue($csv->isOpen());
        $csv->close();
        $this->assertFalse($csv->isOpen());

        $fileInfo = new SplFileInfo(__DIR__ . '/files/file_02.csv');
        $csv = new Csv($fileInfo);
        $csv->setContainsHeader(false);
        /** @noinspection PhpUnhandledExceptionInspection */
        $count = $csv->count();
        $this->assertSame(5, $count);
        $this->assertTrue($csv->isOpen());
        $csv->close();
        $this->assertFalse($csv->isOpen());
    }

    /**
     * Tests header() method
     */
    public function testHeader(): void
    {
        $fileInfo = new SplFileInfo(__DIR__ . '/files/file_01.csv');
        $csv = new Csv($fileInfo);
        /** @noinspection PhpUnhandledExceptionInspection */
        $header = $csv->header();
        $this->assertSame(['field1', 'field2', 'field3'], $header);
        $this->assertTrue($csv->isOpen());
        $csv->close();
        $this->assertFalse($csv->isOpen());

        $fileInfo = new SplFileInfo(__DIR__ . '/files/file_02.csv');
        $csv = new Csv($fileInfo);
        /** @noinspection PhpUnhandledExceptionInspection */
        $header = $csv->header();
        $this->assertSame(['a', 'b', 'c'], $header);
        $this->assertTrue($csv->isOpen());
        $csv->close();
        $this->assertFalse($csv->isOpen());

        $fileInfo = new SplFileInfo(__DIR__ . '/files/file_02.csv');
        $csv = new Csv($fileInfo);
        $csv->setContainsHeader(false);
        /** @noinspection PhpUnhandledExceptionInspection */
        $header = $csv->header();
        $this->assertNull($header);
        $this->assertFalse($csv->isOpen());

        $fileInfo = new SplFileInfo(__DIR__ . '/files/file_04.csv');
        $csv = new Csv($fileInfo);
        /** @noinspection PhpUnhandledExceptionInspection */
        $header = $csv->header();
        $this->assertNull($header);
        $this->assertTrue($csv->isOpen());
    }

    /**
     * Tests rows() method
     */
    public function testRows(): void
    {
        $fileInfo = new SplFileInfo(__DIR__ . '/files/file_01.csv');
        $csv = new Csv($fileInfo);
        /** @noinspection PhpUnhandledExceptionInspection */
        $rowsGenerator = $csv->rows();
        foreach ($rowsGenerator as $lineCount => $row) {
            switch ($lineCount) {
                case 1:
                    $this->assertSame(['field1' => 'a', 'field2' => 'b', 'field3' => 'c'], $row);
                    break;
                case 2:
                    $this->assertSame(['field1' => 'd', 'field2' => 'e', 'field3' => 'f'], $row);
                    break;
                case 3:
                    $this->assertSame(['field1' => 'g', 'field2' => 'h', 'field3' => 'i'], $row);
                    break;
                case 4:
                    $this->assertSame(['field1' => 'j', 'field2' => 'k', 'field3' => 'l'], $row);
                    break;
                case 5:
                    $this->assertSame(['field1' => 'm', 'field2' => 'n', 'field3' => 'o'], $row);
                    break;
            }
        }
        $this->assertSame(5, $rowsGenerator->getReturn());
        $this->assertTrue($csv->isOpen());
        $csv->close();
        $this->assertFalse($csv->isOpen());

        $fileInfo = new SplFileInfo(__DIR__ . '/files/file_02.csv');
        $csv = new Csv($fileInfo);
        $csv->setContainsHeader(false);
        /** @noinspection PhpUnhandledExceptionInspection */
        $rowsGenerator = $csv->rows();
        foreach ($rowsGenerator as $lineCount => $row) {
            switch ($lineCount) {
                case 1:
                    $this->assertSame(['a', 'b', 'c'], $row);
                    break;
                case 2:
                    $this->assertSame(['d', 'e', 'f'], $row);
                    break;
                case 3:
                    $this->assertSame(['g', 'h', 'i'], $row);
                    break;
                case 4:
                    $this->assertSame(['j', 'k', 'l'], $row);
                    break;
                case 5:
                    $this->assertSame(['m', 'n', 'o'], $row);
                    break;
            }
        }
        $this->assertSame(5, $rowsGenerator->getReturn());
        $this->assertTrue($csv->isOpen());
        $csv->close();
        $this->assertFalse($csv->isOpen());
    }

    /**
     * Tests process() method
     */
    public function testProcess(): void
    {
        $fileInfo = new SplFileInfo(__DIR__ . '/files/file_01.csv');
        $csv = new Csv($fileInfo);
        /** @noinspection PhpUnhandledExceptionInspection */
        $rowsExamined = $csv->process(
            function (array $row, int $lineCount): bool {
                $this->lineBuffer[$lineCount] = $row;

                return true;
            }
        );
        $expected = [
            1 => ['field1' => 'a', 'field2' => 'b', 'field3' => 'c'],
            2 => ['field1' => 'd', 'field2' => 'e', 'field3' => 'f'],
            3 => ['field1' => 'g', 'field2' => 'h', 'field3' => 'i'],
            4 => ['field1' => 'j', 'field2' => 'k', 'field3' => 'l'],
            5 => ['field1' => 'm', 'field2' => 'n', 'field3' => 'o'],
        ];
        $this->assertSame($expected, $this->lineBuffer);
        $this->assertSame(5, $rowsExamined);
        $this->assertTrue($csv->isOpen());
        $csv->close();
        $this->assertFalse($csv->isOpen());

        $fileInfo = new SplFileInfo(__DIR__ . '/files/file_01.csv');
        $csv = new Csv($fileInfo);
        $this->lineBuffer = [];
        /** @noinspection PhpUnhandledExceptionInspection */
        $rowsExamined = $csv->process(
            function (array $row, int $lineCount): bool {
                $this->lineBuffer[$lineCount] = $row;

                return $lineCount < 3;
            }
        );
        $expected = [
            1 => ['field1' => 'a', 'field2' => 'b', 'field3' => 'c'],
            2 => ['field1' => 'd', 'field2' => 'e', 'field3' => 'f'],
            3 => ['field1' => 'g', 'field2' => 'h', 'field3' => 'i'],
        ];
        $this->assertSame($expected, $this->lineBuffer);
        $this->assertNull($rowsExamined);
        $this->assertTrue($csv->isOpen());
        $csv->close();
        $this->assertFalse($csv->isOpen());

        $fileInfo = new SplFileInfo(__DIR__ . '/files/file_01.csv');
        $csv = new Csv($fileInfo);
        $this->lineBuffer = [];
        /** @noinspection PhpUnhandledExceptionInspection */
        $rowsExamined = $csv->process(
            function (array $row, int $lineCount): bool {
                $this->lineBuffer[$lineCount] = $row;

                return true;
            },
            function (int $lineCount): void {
                foreach ($this->lineBuffer as $line) {
                    foreach ($line as $fieldName => $fieldValue) {
                        if (!isset($this->chunkBuffer[$fieldName])) {
                            $this->chunkBuffer[$fieldName] = '';
                        }

                        $this->chunkBuffer[$fieldName] .= $fieldValue;
                    }
                }
            }
        );
        $expected = [
            'field1' => 'adgjm',
            'field2' => 'behkn',
            'field3' => 'cfilo',
        ];
        $this->assertSame($expected, $this->chunkBuffer);
        $this->assertSame(5, $rowsExamined);
        $this->assertTrue($csv->isOpen());
        $csv->close();
        $this->assertFalse($csv->isOpen());
    }

}
