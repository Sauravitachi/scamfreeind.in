<?php

namespace App\Traits;

use App\Exceptions\ExcelFileValidationException;
use Exception;

trait ValidateImportFileStructure
{
    /**
     * Abstract method to get the expected column count.
     */
    abstract public function getExpectedColumns(): int;

    /**
     * Abstract method to get the expected column headings.
     */
    abstract public function getExpectedHeadings(): array;

    /**
     * Validates the structure of the uploaded Excel file.
     * Ensures that the number of columns and the headings match the expectations.
     *
     * @param  array  $records  The row data from the Excel sheet as an associative array.
     * @return void
     *
     * @throws \Exception If the file structure is invalid.
     */
    public function validateFileStructure(array $records): array
    {
        if (empty($records)) {
            throw new ExcelFileValidationException('No records found!');
        }

        $expectedHeadings = $this->getExpectedHeadings();

        $records = array_map(function ($row) use ($expectedHeadings) {
            return array_filter($row, function ($key) use ($expectedHeadings) {
                return in_array($key, $expectedHeadings);
            }, ARRAY_FILTER_USE_KEY);
        }, $records);

        // Check the number of columns in the first row (headings)
        $columnCount = count($records[0]);

        if ($columnCount !== $this->getExpectedColumns()) {
            // Handle the error (throw an exception or log it)
            throw new ExcelFileValidationException('Invalid number of columns. Expected '.$this->getExpectedColumns().', got '.$columnCount);
        }

        // Check the headings
        $headings = array_keys($records[0]); // Get the headings from the first row
        $invalidHeadings = array_diff($expectedHeadings, $headings); // Compare expected headings with actual headings

        if (! empty($invalidHeadings)) {
            // Handle invalid headings (throw an exception or log it)
            throw new ExcelFileValidationException('Invalid headings. Expected: '.implode(', ', $expectedHeadings).'. Found: '.implode(', ', $headings));
        }

        return $records;
    }
}
