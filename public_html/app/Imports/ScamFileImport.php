<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class ScamFileImport implements ToCollection, WithHeadingRow
{
    /**
     * Process the imported collection
     * Expected columns: phone, first_name, last_name, full_name, your_loss_amount, ad_id
     * 
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        // The collection is processed in the ExcelImport Livewire component
        // This class handles the Excel file parsing with headers
    }
    
    /**
     * Specify the heading row for the Excel file
     * 
     * @return int
     */
    public function headingRow(): int
    {
        return 1;
    }
}
