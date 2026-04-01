<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;
use App\Models\Scam;

$phoneNumber = '9117442498';
$customer = Customer::where('phone_number', $phoneNumber)->first();

if (!$customer) {
    echo "Customer NOT found for phone number: $phoneNumber\n";
    // Try with 'in' country code explicitly
    $customer = Customer::where('phone_number', $phoneNumber)->where('country_code', 'in')->first();
    if ($customer) {
        echo "Customer found with 'in' country code.\n";
    }
}

if ($customer) {
    echo "Customer found: " . $customer->full_name . " (ID: " . $customer->id . ")\n";
    $scams = $customer->scams()->with(['salesAssignee', 'draftingAssignee', 'salesStatus', 'draftingStatus'])->get();
    echo "Number of scams: " . $scams->count() . "\n";
    foreach ($scams as $scam) {
        echo "Scam ID: " . $scam->id . "\n";
        echo "Sales Assignee: " . ($scam->salesAssignee->name ?? 'None') . " (ID: " . ($scam->salesAssignee->id ?? 'None') . ")\n";
        echo "Drafting Assignee: " . ($scam->draftingAssignee->name ?? 'None') . " (ID: " . ($scam->draftingAssignee->id ?? 'None') . ")\n";
        echo "Sales Status: " . ($scam->salesStatus->title ?? 'None') . " (Bypass: " . ($scam->salesStatus->bypass_enquiry ? 'Yes' : 'No') . ")\n";
        echo "Drafting Status: " . ($scam->draftingStatus->title ?? 'None') . " (Bypass: " . ($scam->draftingStatus->bypass_enquiry ? 'Yes' : 'No') . ")\n";
    }
} else {
    echo "No customer found for $phoneNumber even with 'in' code.\n";
    // List some customers to see the format
    $someCustomers = Customer::limit(5)->get();
    echo "Some customers in db:\n";
    foreach ($someCustomers as $c) {
        echo $c->phone_number . " (" . $c->country_code . ")\n";
    }
}
