<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;
use App\Models\User;

$phoneNumber = '9999999999';
$customer = Customer::firstOrCreate(
    ['phone_number' => $phoneNumber],
    ['first_name' => 'Unassigned', 'last_name' => 'Customer', 'country_code' => 'in']
);

echo "Customer ID: " . $customer->id . "\n";
echo "Scams count: " . $customer->scams()->count() . "\n";

// Get list of Sales Executives
$salesExecs = User::role('Sales Executive')->get(['id', 'name']);
echo "Sales Executives count: " . $salesExecs->count() . "\n";
foreach ($salesExecs as $s) {
    echo " - " . $s->name . " (ID: " . $s->id . ")\n";
}
