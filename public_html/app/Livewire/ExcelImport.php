<?php



namespace App\Livewire;



use Livewire\Component;

use Livewire\WithFileUploads;

use Maatwebsite\Excel\Facades\Excel;

use App\Imports\ScamFileImport;

use App\Models\Customer;
    
use App\Models\Scam;

use App\Models\ScamSource;

use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Str;

use Carbon\Carbon;



class ExcelImport extends Component

{

    use WithFileUploads;



    public $file;

   

    public $previewData = [];

    public $selectedRows = [];

    public $selectAll = true;



    public $processedCount = 0;

    public $skippedCount = 0;

    public $faultyCount = 0;

    public $totalCount = 0;



    public $unique_phone_number = true;

    public $skip_existing_phone_number = true;

    public $previewKey = null;

    public $previewLimit = 10000;



    public $editIndex = null;

    public $editRow = [];

    public $showEditModal = false;





    // Toggle all checkboxes

    public function updatedSelectAll($value)

    {

        $this->selectedRows = $value ? array_keys($this->previewData) : [];

    }



    // Preview Excel file

    public function processFile()

    {

        if (!$this->file) return;



        $collection = Excel::toCollection(new ScamFileImport, $this->file->getRealPath())->first();

        if (!$collection) return;



        $customerColumns = Schema::getColumnListing('customers');

        $scamColumns = Schema::getColumnListing('scams');

        $existingPhones = Customer::pluck('phone_number')->toArray();



        $rows = collect($collection)->map(function ($row) {

        $row = collect($row)->mapWithKeys(fn($v, $k) => [strtolower(trim($k)) => $v])->toArray();

        $phone = $this->normalizePhone($row['phone'] ?? null);
        $amount = $this->parseAmount($row['your_loss_amount'] ?? null);
        $firstName = $row['first_name'] ?? $row['full_name'] ?? null;
        $lastName = $row['last_name'] ?? null;

        return [
            'phone' => $phone ?? '',
            'first_name' => $firstName ?? '',
            'last_name' => $lastName ?? '',
            'your_loss_amount' => $amount ?? 0,
            'ad_id' => $row['ad_id'] ?? '',
            'skip' => false, // 🔥 never skip here
            'faulty' => !$phone,
        ];
    });




        // Remove skipped rows if checkbox is checked

       


        // Cache full dataset and only preview a limited slice to keep payload small

        $dataRows = array_values($rows->toArray());

        $this->previewKey = 'excel_import_'.Str::uuid()->toString();

        Cache::put($this->previewKey, $dataRows, now()->addMinutes(30));



        $this->previewData = array_slice($dataRows, 0, $this->previewLimit);

        $this->selectedRows = array_keys($this->previewData);

        $this->selectAll = true;

       

        // Set counts based on full dataset

        $this->totalCount = count($dataRows);

        $this->processedCount = 0;

        $this->skippedCount = collect($dataRows)->where('skip', true)->count();

        $this->faultyCount = collect($dataRows)->where('faulty', true)->count();



        // Clear file to prevent JSON serialization errors

        $this->reset('file');

    }



    // Import selected rows safely using chunks and transaction

    public function import()

    {

        $processed = 0;

        $skipped = 0;

        $faulty = 0;



        $selected = collect($this->previewData)->only($this->selectedRows)->values();

        if ($selected->isEmpty()) return;



        $chunkSize = 100; // Adjust for large files



        $selected->chunk($chunkSize)->each(function ($chunk) use (&$processed, &$skipped, &$faulty) {

            DB::transaction(function () use ($chunk, &$processed, &$skipped, &$faulty) {

           foreach ($chunk as $row) {

    if ($row['faulty']) {
        $faulty++;
        continue;
    }

    // 1️⃣ Skip existing customer if checkbox ON
    if ($this->skip_existing_phone_number) {
        if (Customer::where('phone_number', $row['phone'])->exists()) {
            $skipped++;
            continue;
        }
    }

    // 2️⃣ Create or get customer
    $customer = Customer::firstOrCreate(
        ['phone_number' => $row['phone']],
        ['first_name' => $row['first_name'], 'last_name' => $row['last_name'] ?? '']
    );

    // 3️⃣ Prevent duplicate scam
    if ($this->unique_phone_number && Scam::where('customer_id', $customer->id)->exists()) {
        $skipped++;
        continue;
    }

    $source = ScamSource::where('slug', 'excel_sheet_import')->first();

    Scam::create([
        'customer_id' => $customer->id,
        'scam_type_id' => 8,
        'scam_source_id' => $source?->id,
        'scam_amount' => $row['your_loss_amount'] ?? null,
        'customer_description' => null,
        'is_duplicate' => 0,
    ]);

    $processed++;
}



            });

        });



        // Reset file and preview

        if ($this->previewKey) {

            Cache::forget($this->previewKey);

        }

        $this->reset(['file', 'previewData', 'selectedRows', 'selectAll', 'totalCount']);

        $this->previewKey = Str::uuid()->toString();

        session()->flash(

            'success',

            "Import completed: {$processed} imported, {$skipped} skipped (existing), {$faulty} faulty records."

        );

    }



    // Import entire cached dataset (not only the preview slice)

    public function importAll()

    {

        $processed = 0;

        $skipped = 0;

        $faulty = 0;



        if (!$this->previewKey) return;



        $allRows = Cache::get($this->previewKey, []);

        if (empty($allRows)) return;



        $chunkSize = 200; // handle large lists safely



        collect($allRows)->chunk($chunkSize)->each(function ($chunk) use (&$processed, &$skipped, &$faulty) {

            DB::transaction(function () use ($chunk, &$processed, &$skipped, &$faulty) {

               foreach ($chunk as $row) {

    if ($row['faulty']) {
        $faulty++;
        continue;
    }

    // 1️⃣ Skip existing customer if checkbox ON
    if ($this->skip_existing_phone_number) {
        if (Customer::where('phone_number', $row['phone'])->exists()) {
            $skipped++;
            continue;
        }
    }

    // 2️⃣ Create or get customer
    $customer = Customer::firstOrCreate(
        ['phone_number' => $row['phone']],
        ['first_name' => $row['first_name'], 'last_name' => $row['last_name'] ?? '']
    );

    // 3️⃣ Prevent duplicate scam
    if ($this->unique_phone_number && Scam::where('customer_id', $customer->id)->exists()) {
        $skipped++;
        continue;
    }

    $source = ScamSource::where('slug', 'excel_sheet_import')->value('id');

    Scam::create([
        'customer_id' => $customer->id,
        'scam_type_id' => 8,
        'scam_source_id' => $source,
        'scam_amount' => $row['your_loss_amount'] ?? null,
        'customer_description' => null,
        'is_duplicate' => 0,
    ]);

    $processed++;
}


            });

        });



        if ($this->previewKey) {

            Cache::forget($this->previewKey);

        }



        $this->reset(['file', 'previewData', 'selectedRows', 'selectAll', 'totalCount']);

    $this->previewKey = Str::uuid()->toString();



        session()->flash(

            'success',

            "Import completed: {$processed} imported, {$skipped} skipped (existing), {$faulty} faulty records."

        );

    }



    private function normalizePhone($phone)

    {

        if (!$phone) return null;

        // Handle Facebook format: p:+916001138202 or p:u know who i'm

        if (str_starts_with($phone, 'p:')) {

            $phone = substr($phone, 2);

        }

        $phone = preg_replace('/[^\d]/', '', $phone);

        if (strlen($phone) > 10 && substr($phone, 0, 2) === '91') $phone = substr($phone, -10);

        return strlen($phone) === 10 ? $phone : null;

    }

    private function parseDate($value)
    {
        if (!$value) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d H:i:s');
            }
            
            // Handle d/m/Y format like 13/11/2025
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}/', $value)) {
                return \Carbon\Carbon::createFromFormat('d/m/Y', explode(' ', $value)[0])->startOfDay()->format('Y-m-d H:i:s');
            }

            return \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

   private function parseAmount($value)

    {

    if (!$value) {

    return null;

    }



    $value = strtolower(str_replace(',', '', trim($value)));



    // Handle shorthand: 100k, 1m, 2l

    if (str_contains($value, 'k')) return floatval(str_replace('k', '', $value)) * 1000;

    if (str_contains($value, 'm')) return floatval(str_replace('m', '', $value)) * 1000000;

    if (str_contains($value, 'l')) return floatval(str_replace('l', '', $value)) * 100000;



    // Only allow numbers (integer or decimal)

    if (!preg_match('/^\d+(\.\d+)?$/', $value)) {

    return null;

    }



    $amount = floatval($value);



    return $amount == 0.0 ? null : $amount;

    }



    public function updateCounts()

    {

        $this->totalCount = count($this->previewData);

        $this->processedCount = 0;

        $this->skippedCount = collect($this->previewData)->where('skip', true)->count();

        $this->faultyCount = collect($this->previewData)->where('faulty', true)->count();

    }

    public function editFaultyRow($index)

{

    $this->editIndex = $index;

    $this->editRow = $this->previewData[$index];

    $this->showEditModal = true;

}



public function saveFaultyRow()

{

    $phone = $this->normalizePhone($this->editRow['phone']);

    if (!$phone) {

        $this->addError('editRow.phone', 'Invalid phone number');

        return;

    }



    // 1️⃣ Update the edited row locally

    $this->editRow['phone'] = $phone;

    $this->editRow['faulty'] = false;



    // 2️⃣ Update previewData

    $this->previewData[$this->editIndex] = $this->editRow;



    if ($this->previewKey) {

        $cached = Cache::get($this->previewKey, []);

        $cached[$this->editIndex] = $this->editRow;

        Cache::put($this->previewKey, $cached, now()->addMinutes(30));

    }



    // Close modal

    $this->showEditModal = false;

}





    public function render()

    {

        return view('livewire.excel-import');

    }

}



