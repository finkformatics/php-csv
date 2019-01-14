# Changelog
All notable changes to `php-csv` will be documented in this file

## 0.2.2 - 2018-01-14
### Fixed
- `$csv->rows()` wasn't able to deal with empty lines at the end of the file.

## 0.2.1 - 2018-01-14
### Fixed
- `$csv->process($rowCallback, $chunkCallback)` was type hinted as `\Closure`, now as `callable`

## 0.2.0 - 2018-01-14
### Added
- Generators will now return number of lines, if not aborted
- `$csv->process()` will now return number of lines, if not aborted 

## 0.1.0 - 2018-01-13
Initial Release of `php-csv`