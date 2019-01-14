<?php
declare(strict_types=1);
/**
 * File Csv.php
 *
 * @author Lukas Werner <kontakt@lwerner.de>
 * @since 13.01.2019
 */

namespace PhpCsv;

use \Generator;
use \SplFileInfo;

use PhpCsv\Exception\FileNotFoundException;
use PhpCsv\Exception\InvalidStateException;
use PhpCsv\Exception\ReadLineException;

/**
 * Class Csv
 *
 * @package PhpCsv
 */
class Csv
{

    /**
     * Default chunk size
     */
    public const DEFAULT_CHUNK_SIZE = 1000;

    /**
     * @var SplFileInfo
     */
    private $fileInfo;

    /**
     * @var resource
     */
    private $handle;

    /**
     * @var int
     */
    private $chunkSize = self::DEFAULT_CHUNK_SIZE;

    /**
     * @var bool
     */
    private $containsHeader = true;

    /**
     * @var array|null
     */
    private $header;

    /**
     * @var int
     */
    private $lineLength;

    /**
     * @var string
     */
    private $delimiter;

    /**
     * @var string
     */
    private $enclosure = '"';

    /**
     * @var string
     */
    private $escape = "\\";

    /**
     * @var int|null
     */
    private $lineCount;

    /**
     * Csv constructor.
     *
     * @param SplFileInfo $fileInfo file info for csv file
     * @param int $lineLength set the line length, default is 1024
     * @param string $delimiter set the delimiter, default is ','
     */
    public function __construct(SplFileInfo $fileInfo, int $lineLength = 1024, string $delimiter = ',')
    {
        $this->fileInfo = $fileInfo;
        $this->lineLength = $lineLength;
        $this->delimiter = $delimiter;
    }

    /**
     * @return int get the currently set chunk size
     */
    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }

    /**
     * @param int $chunkSize set a new chunk size
     *
     * @return Csv fluent interface
     */
    public function setChunkSize(int $chunkSize): self
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    /**
     * Set if csv file contains header row with field names (default is true)
     *
     * @param bool $containsHeader true if csv contains extra row with field names
     *
     * @return Csv fluent interface
     */
    public function setContainsHeader(bool $containsHeader): self
    {
        $this->containsHeader = $containsHeader;

        return $this;
    }

    /**
     * Get the currently set line length
     *
     * @return int currently set line length
     */
    public function getLineLength(): int
    {
        return $this->lineLength;
    }

    /**
     * Set a new line length. The line length should be greater than the longest row (in characters), default is 1024
     *
     * @param int $lineLength new line length
     *
     * @return Csv fluent interface
     */
    public function setLineLength(int $lineLength): Csv
    {
        $this->lineLength = $lineLength;

        return $this;
    }

    /**
     * Get the currently set delimiter
     *
     * @return string currently set delimiter
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * Set a new delimiter, the delimiter divides two fields from each other, default is ','
     *
     * @param string $delimiter new delimiter
     *
     * @return Csv fluent interface
     */
    public function setDelimiter(string $delimiter): Csv
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * Get the currently set enclosure
     *
     * @return string currently set enclosure
     */
    public function getEnclosure(): string
    {
        return $this->enclosure;
    }

    /**
     * Set a new enclosure, the enclosure string will be used to wrap a field which contains reserved characters
     *
     * @param string $enclosure new enclosure
     *
     * @return Csv fluent interface
     */
    public function setEnclosure(string $enclosure): Csv
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    /**
     * Get the currently set escape
     *
     * @return string currently set escape
     */
    public function getEscape(): string
    {
        return $this->escape;
    }

    /**
     * Set a new escape, the escape string will be used to escape special or reserved characters
     *
     * @param string $escape new escape
     *
     * @return Csv fluent interface
     */
    public function setEscape(string $escape): Csv
    {
        $this->escape = $escape;

        return $this;
    }

    /**
     * Returns the line count of the csv file. If not already set, it will be calculated
     *
     * @return int line count (without header, if containsHeader is true)
     *
     * @throws FileNotFoundException if csv file was not found
     * @throws InvalidStateException if open(...) produces any errors
     * @throws ReadLineException if line could not be read
     */
    public function count(): int
    {
        if ($this->lineCount === null) {
            if (!$this->isOpen()) {
                $this->open();
            }

            $this->lineCount = 0;
            $this->process(
                function (array &$row): void {
                    $this->lineCount++;
                }
            );
        }

        return $this->lineCount;
    }

    /**
     * Returns the header. If not read yet, reads it from file and then returns it
     *
     * @param bool $rewind if true, this method will set the file pointer to the former position
     *
     * @return array|null the header as an array, null if no header is available
     * @throws FileNotFoundException if fopen(...) returned false
     * @throws InvalidStateException if file is trying to be opened while already open or fopen(...) did not open file
     * @throws ReadLineException if line could not be read
     */
    public function header(bool $rewind = true): ?array
    {
        if ($this->header === null && $this->containsHeader) {
            if (!$this->isOpen()) {
                $this->open();
            }

            $position = ftell($this->handle);
            rewind($this->handle);
            $this->header = $this->readLine();

            if ($rewind) {
                fseek($this->handle, $position);
            }
        }

        return $this->header;
    }

    /**
     * Get rows as a generator, you can iterate over
     *
     * @return Generator
     * @throws FileNotFoundException if file wasn't found
     * @throws InvalidStateException if there was an error with open()
     * @throws ReadLineException if a line could not be read successfully
     */
    public function rows(): Generator
    {
        if (!$this->isOpen()) {
            $this->open();
        }

        if ($this->containsHeader) {
            $header = $this->header(false);
        } else {
            rewind($this->handle);
        }

        $lineCount = 0;
        while (!feof($this->handle)) {
            $row = $this->readLine();
            if ($row === null) {
                break;
            }

            if ($this->containsHeader && isset($header) && count($header) !== count($row)) {
                throw new ReadLineException(
                    sprintf('Header field number and row field number do not match at line %d.', $lineCount + 1)
                );
            }

            $lineCount++;
            if ($this->containsHeader && isset($header)) {
                yield $lineCount => array_combine($header, $row);
            } else {
                yield $lineCount => $row;
            }
        }

        return $lineCount;
    }

    /**
     * Processes csv file by using callbacks. $rowCallback will be executed for each row, $chunkCallback, if set,
     * will be executed after each chunk. Chunk size can be set.
     *
     * @param callable $rowCallback necessary row callback to process each row
     * @param callable|null $chunkCallback optional chunk callback to process each chunk
     *
     * @return int|null number of rows examined or null if aborted processing
     * @throws FileNotFoundException if csv file was not found
     * @throws InvalidStateException if open(...) produces errors
     * @throws ReadLineException if line could not be read
     */
    public function process(callable $rowCallback, ?callable $chunkCallback = null): ?int
    {
        $lineCount = 0;
        $rowsGenerator = $this->rows();
        $aborted = false;
        foreach ($rowsGenerator as $lineCount => $row) {
            if ($rowCallback($row, $lineCount) === false) {
                $aborted = true;
                break;
            }

            if ($chunkCallback !== null && $lineCount % $this->chunkSize === 0) {
                $chunkCallback($lineCount);
            }
        }

        if ($chunkCallback !== null && $lineCount % $this->chunkSize !== 0) {
            $chunkCallback($lineCount);
        }

        if ($aborted) {
            return null;
        }

        return (int)$rowsGenerator->getReturn();
    }

    /**
     * Checks if file was opened
     *
     * @return bool true, if file is opened currently, false if not
     */
    public function isOpen(): bool
    {
        return is_resource($this->handle);
    }

    /**
     * Closes the csv file
     *
     * @return Csv fluent interface
     */
    public function close(): self
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }

        $this->handle = null;

        return $this;
    }

    /**
     * Opens the csv file
     *
     * @return Csv fluent interface
     * @throws FileNotFoundException if fopen(...) returned false
     * @throws InvalidStateException if file is already open
     */
    private function open(): self
    {
        if ($this->isOpen()) {
            throw new InvalidStateException(
                sprintf('open() call even though file "%s" is already open!', $this->fileInfo->getPathname())
            );
        }

        if (!file_exists($this->fileInfo->getPathname())) {
            throw new FileNotFoundException(sprintf('File "%s" not found.', $this->fileInfo->getPathname()));
        }

        $this->handle = fopen($this->fileInfo->getPathname(), 'r');

        return $this;
    }

    /**
     * Read a line from file and return as array
     *
     * @return array|null the line as an numerically indexed array with the field values as elements or null,
     *                    if no lines are available
     * @throws InvalidStateException if file is not open
     * @throws ReadLineException if line could not be read
     */
    private function readLine(): ?array
    {
        if (!$this->isOpen()) {
            throw new InvalidStateException(
                sprintf('readLine() is called even though file "%s" is not open.', $this->fileInfo->getPathname())
            );
        }

        $row = fgetcsv($this->handle, $this->lineLength, $this->delimiter, $this->enclosure, $this->escape);
        if ($row === false) {
            return null;
        }

        if ($row === null) {
            throw new ReadLineException(
                sprintf(
                    'Unknown error occurred, while trying to read line from file "%s"',
                    $this->fileInfo->getPathname()
                )
            );
        }

        if (($row[0] ?? null) === null) {
            return null;
        }

        return array_map('trim', $row);
    }

}
