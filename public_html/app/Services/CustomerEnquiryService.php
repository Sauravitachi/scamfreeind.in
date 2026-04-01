<?php

namespace App\Services;

use App\Enums\CustomerEnquiryStatusType;
use App\Filters\CustomerEnquiryFilter;
use App\Http\Requests\Admin\ChangeCustomerEnquiryStatusRequest;
use App\Models\CustomerEnquiry;
use App\Models\CustomerEnquiryFreeze;
use App\Models\CustomerEnquiryStatusRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;

class CustomerEnquiryService extends Service
{
    public function dataTable(Request $request): EloquentDataTable
    {
        $user = $request->user();
        $userRole = $user->getRoleString();

        $query = CustomerEnquiry::query();

        $query->with('customer:id,first_name,last_name,dial_code,phone_number,email');

        $query->with('source:id,slug,title');

        $query->with('customer.scams', function (HasMany $q): HasMany {
            return $q
                ->leftJoin('scam_types', 'scams.scam_type_id', '=', 'scam_types.id')
                ->select([
                    'scams.id',
                    'scams.track_id',
                    'scams.customer_id',
                    'scams.scam_type_id',
                    'scams.scam_amount',
                    'scams.sales_assignee_id',
                    'scams.drafting_assignee_id',
                    'scams.sales_status_id',
                    'scams.drafting_status_id',
                    'scam_types.title as scam_type',
                ])
                ->with([
                    'salesAssignee:id',
                    'draftingAssignee:id',
                    'salesStatus',
                    'draftingStatus',
                    'draftingStatus.previousStatuses',
                    'draftingStatus.nextStatuses',
                    'draftingStatusRecord:id,scam_id,review',
                ])
                ->where('scams.is_duplicate', false)
                ->whereNull('recycled_at')
                ->orderBy('scams.created_at', 'desc')
                ->orderBy('scams.id', 'desc')
                ->take(1);
        });

        $query->where('occurrence', '>', 0);

        $query->when(
            $userRole === 'drafting' && $this->hasFrozenEnquiries($user, $userRole),
            function (Builder $q) {
                $q->whereNull('drafting_status_id')
                    ->orWhereHas('draftingStatus', function (Builder $q2) {
                        $q2->where('consider_resolved', false);
                    });
            }
        );

        if ($userRole === 'sales') {
            $query->whereSalesAssignee($user->id);
        } elseif ($userRole === 'drafting') {
            $query->whereDraftingAssignee($user->id);
        } else {
            if (($key = $request->input('records_type')) && in_array($key, range(1, 2))) {
                if ($key == 1) {
                    $query->whereNotNull('manually_assigned_at');
                } elseif ($key == 2) {
                    $query->whereNull('manually_assigned_at');
                }
            }

            if ($request->filled('assigneeType')) {
                $bypassed = $request->boolean('bypassed', false);
                if ($request->assigneeType === 'sales') {
                    $query->whereSalesAssignee(bypassed: $bypassed);
                } else {
                    $query->whereDraftingAssignee(bypassed: $bypassed);
                }
            }
        }

        CustomerEnquiryFilter::apply($query, $request);

        $table = datatables()->eloquent($query);

        $table->addColumn('customer_info', fn (CustomerEnquiry $ce): array => [$ce->customer->fullName, $ce->customer->fullPhoneNumber]);

        $table->editColumn('customer', function (CustomerEnquiry $ce): ?array {
            $ce->customer->append('full_name_with_full_phone_number');

            return $ce->customer?->toArray();
        });

        $table->addColumn('scam', fn (CustomerEnquiry $ce): ?array => $ce->customer->scams->first()?->toArray());

        $table->addColumn('scam_amount', function (CustomerEnquiry $ce): ?string {
            $amount = $ce->customer->scams->first()?->scam_amount;

            return $amount ? format_amount($amount) : null;
        });
        $table->addColumn('sales_assignee_id', fn (CustomerEnquiry $ce): ?int => $ce->customer->scams->first()?->salesAssignee?->id);
        $table->addColumn('drafting_assignee_id', fn (CustomerEnquiry $ce): ?int => $ce->customer->scams->first()?->draftingAssignee?->id);

        $table->addColumn('scam_sales_status_id', fn (CustomerEnquiry $ce): ?int => $ce->customer->scams->first()?->salesStatus?->id);
        $table->addColumn('scam_drafting_status_id', fn (CustomerEnquiry $ce): ?int => $ce->customer->scams->first()?->draftingStatus?->id);

        $table->filterColumn('customer_info', function (Builder $query, string $keyword): void {
            $query->whereHas('customer', function (Builder $query) use ($keyword) {
                $query->whereSearch($keyword);
            });
        });

        $table->orderColumn('scam_amount', function (Builder $query, string $order) {
            $query->join('customers', 'customers.id', '=', 'customer_enquiries.customer_id')
                ->join('scams', function ($join) {
                    $join->on('scams.customer_id', '=', 'customers.id')
                        ->where('scams.is_duplicate', 0)
                        ->whereNull('scams.recycled_at');
                })
                ->orderBy('scams.scam_amount', $order);
        });

        $table->orderColumn('customer_info', function (Builder $query, string $order): void {
            $query->join('customers', 'customer_enquiries.customer_id', '=', 'customers.id')
                ->orderBy('customers.first_name', $order)
                ->orderBy('customers.last_name', $order);
        });

        $table->editColumn('created_at', fn (CustomerEnquiry $ce): ?string => format_date($ce->created_at));
        $table->orderColumn('created_at', fn (Builder $q, string $o) => $q->orderBy('created_at', $o)->orderBy('id', $o));

        return $table;
    }

    public function changeStatus(ChangeCustomerEnquiryStatusRequest $request, CustomerEnquiry $enquiry): bool
    {
        return DB::transaction(function () use ($request, $enquiry): bool {
            $user = $request->user();

            $canUpdate = true;

            if ($user->userType() === 'sales') {
                $canUpdate = $enquiry->customer->scams()->where('sales_assignee_id', $user->id)->exists();
            } elseif ($user->userType() === 'drafting') {
                $canUpdate = $enquiry->customer->scams()->where('drafting_assignee_id', $user->id)->exists();
            }

            $data = $request->validated();
            $type = $data['type'];

            return $canUpdate ? $enquiry->update([
                "{$type}_status_id" => $data['status_id'],
                'remark' => $data['remark'] ?? null,
            ]) : false;
        });
    }

    public function getEnquiryTitle(CustomerEnquiry $customerEnquiry): string
    {
        $title = $customerEnquiry->customer->full_name_with_full_phone_number;

        $scamAmount = $customerEnquiry->customer->scams()->orderBy('scam_amount', 'desc')->first(['customer_id', 'scam_amount'])?->scam_amount ?? null;

        if (! is_null($scamAmount)) {
            $title .= ' | '.format_amount($scamAmount);
        }

        return $title;
    }

    public function handleNewEnquiyCreatingEvent(CustomerEnquiry $customerEnquiry): void
    {
        // Reset previous enquiries to 0
        CustomerEnquiry::where('customer_id', $customerEnquiry->customer_id)
            ->update(['occurrence' => 0]);

        // Count previous ones (after resetting them)
        $count = CustomerEnquiry::where('customer_id', $customerEnquiry->customer_id)->count();

        // Set occurrence for this (new) one
        $customerEnquiry->occurrence = $count + 1;
    }

    public function handleEnquiryUpdatedEvent(CustomerEnquiry $enquiry, bool $save = false): void
    {

        $authId = Auth::id();

        if ($enquiry->isDirty('drafting_status_id')) {

            if ($enquiry->drafting_status_id) {
                $enquiry->drafting_status_updated_at = now();
                CustomerEnquiryFreeze::checkAndTryFreezeRelease($enquiry, CustomerEnquiryStatusType::DRAFTING);
            }

            CustomerEnquiryStatusRecord::logRecord(customerEnquiry: $enquiry, type: CustomerEnquiryStatusType::DRAFTING, causer: $authId);
        }

        if ($enquiry->isDirty('sales_status_id')) {

            if ($enquiry->sales_status_id) {
                $enquiry->sales_status_updated_at = now();
            }

            CustomerEnquiryStatusRecord::logRecord(customerEnquiry: $enquiry, type: CustomerEnquiryStatusType::SALES, causer: $authId);
        }

        if ($save) {
            $enquiry->saveQuietly();
        }
    }

    public function isTimeBetween($now, $start, $end)
    {
        // Overnight range, like 19:00 to 10:00
        if ($start > $end) {
            return $now >= $start || $now < $end;
        }

        // Normal range, like 10:00 to 19:00
        return $now >= $start && $now < $end;
    }

    public function hasFrozenEnquiries(User $user, string $userRole): bool
    {
        if (
            $userRole !== 'drafting' ||
            ! (setting('freeze_enquiry_threshold', null)) ||
            $user->isFreezeForceReleased()
        ) {
            return false;
        }

        $query = CustomerEnquiryFreeze::whereUser($user, $userRole);

        $officeStartTime = setting('office_start_time');
        $officeEndTime = setting('office_end_time');
        $startingRelaxationHours = (int) setting('starting_enquiries_relaxation_hours');

        if ($officeStartTime && $officeEndTime) {
            // Get current time and office start/end times as Carbon instances

            $officeStart = Carbon::createFromFormat('H:i', $officeStartTime)->format('H:i');
            $officeEnd = Carbon::createFromFormat('H:i', $officeEndTime)->format('H:i');

            // Check if a record exists with created_at outside of office hours
            $cq = $query->clone()->where(function ($q) use ($officeStart, $officeEnd) {
                $q->where(function ($q2) use ($officeStart, $officeEnd) {
                    // For Mon-Sat: skip office hours
                    $q2->where(function ($q3) use ($officeStart, $officeEnd) {
                        $q3->whereTime('created_at', '<', $officeStart)
                            ->orWhereTime('created_at', '>', $officeEnd);
                    })
                        ->where(DB::raw('DAYOFWEEK(created_at)'), '!=', 1); // Not Sunday
                })
                    ->orWhere(function ($q2) use ($officeStart, $officeEnd) {
                        // For Sunday: include only within office hours
                        $q2->whereTime('created_at', '>=', $officeStart)
                            ->whereTime('created_at', '<=', $officeEnd)
                            ->where(DB::raw('DAYOFWEEK(created_at)'), '=', 1); // Only Sunday
                    });
            });

            // If freeze is outside of office hours
            if ($cq->exists()) {

                $now = now();
                $nowTime = $now->copy()->format('H:i');

                if ($this->isTimeBetween($nowTime, $officeEndTime, $officeStartTime)) {
                    return false;
                }

                if ($startingRelaxationHours) {

                    $todayOfficeStart = Carbon::createFromFormat('H:i', $officeStartTime)->setDateFrom($now);
                    $relaxationPeriodEnd = $todayOfficeStart->copy()->addHours($startingRelaxationHours);
                    $relaxationPeriodStart = $todayOfficeStart;

                    if ($now->between($relaxationPeriodStart, $relaxationPeriodEnd)) {
                        return false;
                    }

                }

                return true;
            }
        }

        return $query->exists();
    }
}
