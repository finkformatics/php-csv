# php-csv
This library focuses on simplicity and memory efficiency when processing CSV files in PHP. 

## Features
- Simple API
- Read CSV files line by line
- Memory efficiency: File is read line by line, no memory issues with large files
- Process CSV using row and chunk callbacks
- Fully documented
- Fully unit tested
- Composer ready

## System Requirements
You need **PHP** >= **7.1.0** to use `php-csv` but the latest stable version of PHP is recommended.

## Install
Install `php-csv` using Composer.

```
$ composer require lukaswerner/php-csv
```

## Use
You can initialize an object of the main `PhpCsv\Csv` class by providing a valid `\SplFileInfo` object to the constructor:
```
$fileInfo = new \SplFileInfo('/path/to/file.csv');
$csv = new \PhpCsv\Csv($fileInfo);
```

You now have an easy to understand API to process your CSV file. 
Each processing method will at first rewind the file, so that you don't have any issues with file pointers.
If your file does not contain a header row, you must call `$csv->setContainsHeader(false);`.

### Number of lines
You can easily count the number of lines in your csv file by calling `$csv->count();`. This method
will only once examine all lines. On second call it will use its previously set value. To avoid that,
you have to create a new object of `\PhpCsv\Csv`.

### Reading header
You can read the header fields by calling `$csv->header();`. This will return an array of strings with header information.
This method will only once examine all lines. On second call it will use its previously set value. To avoid that,
you have to create a new object of `\PhpCsv\Csv`.

### Iterating over rows
You can iterate over all rows of your csv file by doing something like this:
```
foreach ($csv->rows() as $lineNumber => $row) {
    // Do somethine with row
}
```

The `$lineNumber` contains the current line number, starting with `1`. The header (if existing) is not included. 
So a file with a header and one line has `$lineCount = 1` on that line.

The `rows()` method returns a PHP Generator and loops can be continued or stopped as you wish.

### Processing using callbacks
The `php-csv` library offers a comfortable `process()` method to process the CSV file using callbacks. You can do something like this:
```
$csv->process(
    function (array $row, int $lineNumber): bool {
        // Do something with your data
        
        return true; // If you return false here, the processing will stop
    }
);
```

#### Chunking
The callback processing also allows chunking. That means, you have a callback for each row and additionally a callback for each chunk. 
At first you can set the chunk size with `$csv->setChunkSize(500)` (default is 1000), then you call the `process()` method like this:
```
$csv->process(
    function (array $row, int $lineNumber): bool {
        // Do something with your data
        
        return true; // If you return false here, the processing will stop
    },
    function (): void {
        // Will be called e.g. every 500th row
        // you can flush your values to db or something like that
    }
);
```

Please note, that the chunk callback will be called one last time, when you return `false` from the row callback.
That is to be able to flush incomplete chunks. 

## Contributing
Contributions are welcome and will be fully credited. Please feel free to open issues and ask for repository access.

## Changelog
Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License
The GNU General Public License v3.0. Please see [LICENSE](LICENSE) for more information.