<?php

namespace App\Utilities;

use App\Models\CustomerEnquiry;
use App\Models\Scam;
use App\Models\ScamStatusRecord;
use Illuminate\Support\Collection;

class Tinker
{
    public static function run(): void
    {
        \DB::table('scams')->whereIn('sales_status_id', [4, 8])->update(['sales_status_id' => 9]);
    }

    public static function statusUpdateStuff()
    {
        \DB::transaction(function () {

            $scams = Scam::whereNotNull('drafting_assignee_id')->whereNull('drafting_status_updated_at')->get();

            foreach ($scams as $scam) {

                $status = ScamStatusRecord::where('scam_id', $scam->id)->where('status_type', 'drafting')->orderBy('created_at', 'desc')->orderBy('id', 'desc')->limit(1)->first();

                if ($status && ($status->status_id == $scam->drafting_status_id)) {
                    $scam->update(['drafting_status_updated_at' => $status->created_at]);
                } else {
                    $scam->update(['drafting_status_updated_at' => $scam->updated_at]);
                }

            }

            $scams = Scam::whereNotNull('sales_assignee_id')->whereNull('sales_status_updated_at')->get();

            foreach ($scams as $scam) {

                $status = ScamStatusRecord::where('scam_id', $scam->id)->where('status_type', 'sales')->orderBy('created_at', 'desc')->orderBy('id', 'desc')->limit(1)->first();

                if ($status && ($status->status_id == $scam->sales_status_id)) {
                    $scam->update(['sales_status_updated_at' => $status->created_at]);
                } elseif ($scam->sales_assigned_at) {
                    $scam->update(['sales_status_updated_at' => $scam->sales_assigned_at->addMinutes(mt_rand(10, 20))]);
                }
            }

        });
    }

    public static function addOccurrenceInCustomerEnquiries(): void
    {
        $processedCustomers = [];

        CustomerEnquiry::chunk(100, function (Collection $customerEnquiries) use (&$processedCustomers) {
            $customerEnquiries->each(function (CustomerEnquiry $customerEnquiry) use (&$processedCustomers) {
                $customerId = $customerEnquiry->customer_id;

                if (isset($processedCustomers[$customerId])) {
                    return;
                }

                $allCustomerEnquiries = CustomerEnquiry::where('customer_id', $customerId)
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->get();

                $count = $allCustomerEnquiries->count();

                if ($first = $allCustomerEnquiries->first()) {
                    $first->occurrence = $count;
                    $first->save();
                }

                $allCustomerEnquiries->skip(1)->each(function (CustomerEnquiry $enquiry) {
                    $enquiry->occurrence = 0;
                    $enquiry->save();
                });

                $processedCustomers[$customerId] = true;
            });
        });
    }
}
