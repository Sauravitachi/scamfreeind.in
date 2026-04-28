<?php

namespace App\Filters;

use App\Constants\Permission;
use App\Enums\ScamAssigneeType;
use App\Enums\ScamStatusType;
use App\Models\ScamSource;
use App\Models\ScamStatus;
use App\Models\State;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ScamFilter
{
    public static function setData()
    {
        $request = request();
        $user = $request->user();

        if (($scamSourceId = $request->integer('scam_source')) && $scamSource = ScamSource::select(['id', 'title as label'])->find($scamSourceId)) {
            $request->merge(['filter_scam_source' => $scamSource->toArray()]);
        }

        if (($statusId = $request->integer('sales_status_id')) && ($statusId == -1 || ScamStatus::where('id', $statusId)->exists())) {
            $request->merge(['filter_sales_status_id' => $statusId]);
        }

        if (($salesAssigneeId = $request->integer('sales_assignee_id')) && ($salesAssigneeId == -1 || User::whereSales()->where('id', $salesAssigneeId)->exists())) {
            $request->merge(['filter_sales_assignee_id' => $salesAssigneeId]);
        }

        if (($statusId = $request->integer('drafting_status_id')) && ($statusId == -1 || ScamStatus::where('id', $statusId)->exists())) {
            $request->merge(['filter_drafting_status_id' => $statusId]);
        }

        if (($draftingAssigneeId = $request->integer('drafting_assignee_id')) && ($draftingAssigneeId == -1 || User::whereDrafting()->where('id', $draftingAssigneeId)->exists())) {
            $request->merge(['filter_drafting_assignee_id' => $draftingAssigneeId]);
        }

        if (($subAdminId = $request->integer('sub_admin_id')) && ($subAdminId == -1 || User::whereSubAdmin()->where('id', $subAdminId)->exists())) {
            $request->merge(['filter_sub_admin_id' => $subAdminId]);
        }

        if ($stateIds = $request->input('state_id')) {
            $request->merge(['filter_state_id' => $stateIds]);
        }
    }

    public static function apply(Builder $query)
    {
        $request = request();
        $user = $request->user();

        /**
         * Remove duplicate records by phone number
         */
        $key = $request->integer('records_type');

        $showStatusUnassigneRecords = false;

        if ($key && in_array($key, range(1, 3))) {
            if ($key == 1) {
                $query->where('is_duplicate', false);
            } elseif ($key == 2) {
                $query->where('is_duplicate', true);
            } elseif ($key == 3 && $user->can(Permission::STATUS_UNASSIGNED_SCAM_LIST)) {
                $query->where(function ($q) {
                    $q->whereHas('latestSalesStatusUnassignRecord')
                        ->orWhereHas('latestDraftingStatusUnassignRecord')
                        ->orWhereHas('latestServiceStatusUnassignRecord');
                });
                $showStatusUnassigneRecords = true;
            }
        }

        // if (! $showStatusUnassigneRecords) {
        //     $query->whereDoesntHave('latestSalesStatusUnassignRecord')
        //         ->whereDoesntHave('latestDraftingStatusUnassignRecord')
        //         ->whereDoesntHave('latestServiceStatusUnassignRecord');
        // }

        /**
         * Customer Name Filter
         */
        if ($key = trim($request->input('customer_name'))) {
            $query->whereHas('customer', function (Builder $q) use ($request, $key) {
                $reverse = $request->boolean('exclude_customer_name');
                if ($reverse) {
                    $q->where(function ($q) use ($key) {
                        $q->whereNull('first_name')->orWhereNull('last_name')->orWhereRaw(
                            sql: "LOWER(TRIM(CONCAT_WS(' ', first_name, last_name))) NOT LIKE ?",
                            bindings: ['%'.strtolower($key).'%']
                        );
                    });
                } else {
                    $q->whereSearchName($key);
                }
            });
        }

        /**
         * Customer Mobile No. Filter
         */
        if ($key = $request->input('customer_mobile_number')) {
            $reverse = $request->boolean('exclude_customer_mobile_number');
            $query->whereHas('customer', function (Builder $q) use ($key, $reverse): void {
                if ($reverse) {
                    $q->whereRaw(
                        sql: "CONCAT('+', dial_code, ' ', phone_number) NOT LIKE ?",
                        bindings: ["%{$key}%"]
                    );
                } else {
                    $q->whereSearchPhoneNumber($key);
                }
            });
        }

        /**
         * Scam Type Filter
         */
        if ($keyData = $request->input('scam_type_id')) {
            $reverse = $request->boolean('exclude_scam_type_id');
            if (! is_array($keyData)) {
                $keyData = [$keyData];
            }
            $query->{$reverse ? 'whereNotIn' : 'whereIn'}('scam_type_id', $keyData);
        }

        /**
         * Lead Source Filter
         */
        if ($keyData = $request->input('scam_source_id')) {
            if (! is_array($keyData)) {
                $keyData = [$keyData];
            }

            $reverse = $request->boolean('exclude_scam_source_id');
            if ($reverse) {
                $query->where(function (Builder $q) use ($keyData) {
                    $q->whereNull('scam_source_id')
                        ->orWhereNotIn('scam_source_id', $keyData);
                });
            } else {
                $query->whereIn('scam_source_id', $keyData);
            }
        }

        /**
         * State Filter
         */
        if ($keyData = $request->input('state_id')) {
            $reverse = $request->boolean('exclude_state_id');
            if (! is_array($keyData)) {
                $keyData = [$keyData];
            }
            $query->whereHas('customer', function (Builder $q) use ($keyData, $reverse) {
                $q->{$reverse ? 'whereNotIn' : 'whereIn'}('state', $keyData);
            });
        }

        /**
         * Sales Assignee Filter
         */
        if ($request->filled('sales_assignee_id')) {
            self::assigneeFilter(query: $query, request: $request, field: 'sales_assignee_id', assigneeType: ScamAssigneeType::SALES);
        }

        /**
         * Drafting Assignee Filter
         */
        if ($request->filled('drafting_assignee_id')) {
            self::assigneeFilter(query: $query, request: $request, field: 'drafting_assignee_id', assigneeType: ScamAssigneeType::DRAFTING);
        }

        /**
         * Service Assignee Filter
         */
        if ($request->filled('service_assignee_id')) {
            self::assigneeFilter(query: $query, request: $request, field: 'service_assignee_id', assigneeType: ScamAssigneeType::SERVICE);
        }

        /**
         * Sub Admin Filter
         */
        if ($request->filled('sub_admin_id')) {
            self::subAdminFilter($query, $request);
        }

        /**
         * Amount Range Filter
         */
        if ($request->filled('amount_min') || $request->filled('amount_max')) {
            self::amountRangeFilter($query, $request);
        }

        /**
         * Sales Status Filter
         */
        if ($request->filled('sales_status_id')) {
            self::statusFilter(query: $query, request: $request, field: 'sales_status_id');
        }

        /**
         * Sales Status Filter
         */
        if ($request->filled('drafting_status_id')) {
            self::statusFilter(query: $query, request: $request, field: 'drafting_status_id');
        }

        /**
         * Created at range filter
         */
        if ($request->filled('created_at')) {
            self::dateRangeFilter(query: $query, request: $request, field: 'created_at');
        }
        

        if ($request->filled('sales_status_unassigned_assignee_id')) {
            self::statusUnassignedAssigneeFilter(query: $query, request: $request, field: 'sales_status_unassigned_assignee_id', assigneeType: ScamAssigneeType::SALES);
        }
        if ($request->filled('drafting_status_unassigned_assignee_id')) {
            self::statusUnassignedAssigneeFilter(query: $query, request: $request, field: 'drafting_status_unassigned_assignee_id', assigneeType: ScamAssigneeType::DRAFTING);
        }
        if ($request->filled('sales_status_unassigned_status_id')) {
            self::statusUnassignedStatusFilter(query: $query, request: $request, field: 'sales_status_unassigned_status_id', statusType: ScamStatusType::SALES);
        }
        if ($request->filled('drafting_status_unassigned_status_id')) {
            self::statusUnassignedStatusFilter(query: $query, request: $request, field: 'drafting_status_unassigned_status_id', statusType: ScamStatusType::DRAFTING);
        }
        if ($request->filled('sales_status_unassigned_at')) {
            self::statusUnassignedDateFilter(query: $query, request: $request, field: 'sales_status_unassigned_at', statusType: ScamStatusType::SALES);
        }
        if ($request->filled('drafting_status_unassigned_at')) {
            self::statusUnassignedDateFilter(query: $query, request: $request, field: 'drafting_status_unassigned_at', statusType: ScamStatusType::DRAFTING);
        }

        /**
         * Sales Assigned at range filter
         */
        if ($request->filled('sales_assigned_at')) {
            self::dateRangeFilter(query: $query, request: $request, field: 'sales_assigned_at', fieldId: 'sales_assignee_id');
        }

        /**
         * Drafting Assigned at range filter
         */
        if ($request->filled('drafting_assigned_at')) {
            self::dateRangeFilter(query: $query, request: $request, field: 'drafting_assigned_at', fieldId: 'drafting_assignee_id');
        }

        /**
         * Service Assigned at range filter
         */
        if ($request->filled('service_assigned_at')) {
            self::dateRangeFilter(query: $query, request: $request, field: 'service_assigned_at', fieldId: 'service_assignee_id');
        }

        /**
         * Sales Status Updated at range filter
         */
        if ($request->filled('sales_status_updated_at')) {
            self::dateRangeFilter(query: $query, request: $request, field: 'sales_status_updated_at');
        }

        /**
         * Drafting Status Updated at range filter
         */
        if ($request->filled('drafting_status_updated_at')) {
            self::dateRangeFilter(query: $query, request: $request, field: 'drafting_status_updated_at');
        }

        /**
         * Sales Status  Review
         */
        if ($request->filled('sales_status_review')) {
            self::statusReviewFilter($query, $request, ScamStatusType::SALES);
        }

        /**
         * Drafting Status  Review
         */
        if ($request->filled('drafting_status_review')) {
            self::statusReviewFilter($query, $request, ScamStatusType::DRAFTING);
        }

        if ($request->filled('sales_status_review_status')) {
            self::statusReviewStatusFilter($query, $request, ScamStatusType::SALES);
        }

        if ($request->filled('drafting_status_review_status')) {
            self::statusReviewStatusFilter($query, $request, ScamStatusType::DRAFTING);
        }
    }

    private static function statusReviewStatusFilter(Builder $query, Request $request, ScamStatusType $type)
    {
        $statusIds = $request->input("{$type->value}_status_review_status", []);
        $query->whereHas(
            "{$type->value}StatusRecord",
            fn (Builder $q) => $q->whereIn('status_id', $statusIds)
        );
    }

    private static function statusReviewFilter(Builder $query, Request $request, ScamStatusType $type): void
    {
        $reviews = $request->input("{$type->value}_status_review", []);
        $reverse = $request->boolean("exclude_{$type->value}_status_review");
        $query->whereHas("{$type->value}StatusRecord", fn (Builder $q) => $q->{$reverse ? 'whereNotIn' : 'whereIn'}('review', $reviews));
    }

    private static function dateRangeFilter(Builder $query, Request $request, string $field, ?string $fieldId = null): void
    {
        $key = $request->input($field);

        $range = carbon_date_range($key, 'to', expandDates: true);
        $reverse = $request->boolean("exclude_{$field}");

        if ($fieldId) {

            $query->where(function (Builder $q) use ($range, $reverse, $field, $fieldId): void {

                $q->whereNotNull("scams.{$fieldId}")->{$reverse ? 'whereNotBetween' : 'whereBetween'}("scams.{$field}", [$range->start, $range->end]);

            });

        } else {

            $query->{$reverse ? 'whereNotBetween' : 'whereBetween'}("scams.{$field}", [$range->start, $range->end]);
        }
    }

    private static function statusFilter(Builder $query, Request $request, string $field): void
    {
        $keyData = $request->input($field);

        if (! is_array($keyData)) {
            $keyData = [$keyData];
        }

        $reverse = $request->boolean("exclude_{$field}");

        if (! empty($keyData) && ! (count($keyData) == 1 && $keyData[0] === null)) {
            $query->where(function (Builder $q) use ($keyData, $reverse, $field): void {
                in_array('-1', $keyData)
                    ? $q->{$reverse ? 'whereNotNull' : 'whereNull'}($field)
                    : $q->{$reverse ? 'whereNotIn' : 'whereIn'}($field, $keyData);
            });
        }

    }

    private static function assigneeFilter(Builder $query, Request $request, string $field, ScamAssigneeType $assigneeType): void
    {
        $keyData = $request->input($field);

        if (! is_array($keyData)) {
            $keyData = [$keyData];
        }

        $reverse = $request->boolean("exclude_{$field}");
        $history = $request->boolean("history_{$field}");

        $hasNullable = in_array('-1', $keyData);

        if (! empty($keyData) && ! (count($keyData) == 1 && $keyData[0] === null)) {

            $query->where(function (Builder $q) use ($keyData, $reverse, $history, $field, $assigneeType, $hasNullable): void {

                if ($history) {

                    if ($hasNullable) {
                        $q->{$reverse ? 'whereNotNull' : 'whereNull'}($field);
                    }

                    $q->{$reverse ? 'orWhereNotIn' : 'orWhereIn'}($field, $keyData)
                        ->{$reverse ? 'orWhereDoesntHave' : 'orWhereHas'}('assigneeRecords', function (Builder $q) use ($keyData, $reverse, $assigneeType, $hasNullable): void {

                            $q->where('assignee_type', $assigneeType->value)
                                ->where(function ($q) use ($hasNullable, $reverse, $keyData) {

                                    if ($hasNullable) {
                                        $q->{$reverse ? 'whereNotNull' : 'whereNull'}('assignee_id');
                                    }

                                    $q->orWhereIn('assignee_id', $keyData);

                                });

                        });

                } else {

                    if ($hasNullable) {
                        $q->{$reverse ? 'whereNotNull' : 'whereNull'}($field);
                    }

                    $q->{$reverse ? 'orWhereNotIn' : 'orWhereIn'}($field, $keyData);

                }
            });
        }
    }

    private static function statusUnassignedAssigneeFilter(Builder $query, Request $request, string $field, ScamAssigneeType $assigneeType): void
    {
        $keyData = $request->input($field);

        if (! is_array($keyData)) {
            $keyData = [$keyData];
        }

        $reverse = $request->boolean("exclude_{$field}");
        $history = $request->boolean("history_{$field}");

        if (! empty($keyData) && ! (count($keyData) == 1 && $keyData[0] === null)) {

            $query->where(function (Builder $q) use ($history, $keyData, $assigneeType, $reverse): void {

                $relation = $history ? 'statusUnassignRecords' : 'latest'.ucfirst($assigneeType->value).'StatusUnassignRecord';

                $q->whereHas($relation, function (Builder $q) use ($keyData, $assigneeType, $reverse) {
                    $q->when(
                        value: $reverse,
                        callback: fn (Builder $q) => $q->whereNotIn('assignee_id', $keyData),
                        default: fn (Builder $q) => $q->whereIn('assignee_id', $keyData)
                    )->where('status_type', $assigneeType);
                });

            });
        }
    }

    private static function statusUnassignedStatusFilter(Builder $query, Request $request, string $field, ScamStatusType $statusType): void
    {
        $keyData = $request->input($field);

        if (! is_array($keyData)) {
            $keyData = [$keyData];
        }

        $reverse = $request->boolean("exclude_{$field}");

        if (! empty($keyData) && ! (count($keyData) == 1 && $keyData[0] === null)) {

            $relation = 'latest'.ucfirst($statusType->value).'StatusUnassignRecord';

            $query->whereHas($relation, function (Builder $q) use ($keyData, $statusType, $reverse) {
                $q->where(function ($q) use ($keyData, $reverse) {
                    if (in_array('-1', $keyData)) {
                        $q->{$reverse ? 'whereNotNull' : 'whereNull'}('status_id');
                    } else {
                        $q->{$reverse ? 'whereNotIn' : 'whereIn'}('status_id', $keyData);
                    }
                })->where('status_type', $statusType);
            });
        }
    }

    private static function statusUnassignedDateFilter(Builder $query, Request $request, string $field, ScamStatusType $statusType): void
    {
        $key = $request->input($field);

        $range = carbon_date_range($key, 'to', expandDates: true);

        $reverse = $request->boolean("exclude_{$field}");

        $relation = 'latest'.ucfirst($statusType->value).'StatusUnassignRecord';

        $query->whereHas($relation, function (Builder $q) use ($range, $statusType, $reverse) {
            $q->when(
                value: $reverse,
                callback: fn (Builder $q) => $q->whereNotBetween('created_at', [$range->start, $range->end]),
                default: fn (Builder $q) => $q->whereBetween('created_at', [$range->start, $range->end])
            )->where('status_type', $statusType);
        });
    }

    private static function subAdminFilter(Builder $query, Request $request): void
    {
        $keyData = $request->input('sub_admin_id');

        if (! is_array($keyData)) {
            $keyData = [$keyData];
        }

        $reverse = $request->boolean('exclude_sub_admin_id');
        $hasNullable = in_array('-1', $keyData);

        if (! empty($keyData) && ! (count($keyData) == 1 && $keyData[0] === null)) {
            $query->where(function (Builder $q) use ($keyData, $reverse, $hasNullable): void {
                if ($hasNullable) {
                    $q->{$reverse ? 'whereNotNull' : 'whereNull'}('sub_admin_id');
                }
                $q->{$reverse ? 'orWhereNotIn' : 'orWhereIn'}('sub_admin_id', $keyData);
            });
        }
    }

    private static function amountRangeFilter(Builder $query, Request $request): void
    {
        $min = $request->input('amount_min');
        $max = $request->input('amount_max');
        $reverse = $request->boolean('exclude_amount_range');

        if ($reverse) {
            $query->where(function ($q) use ($min, $max) {
                if (filled($min)) {
                    $q->where('scam_amount', '<', $min);
                }
                if (filled($max)) {
                    $q->orWhere('scam_amount', '>', $max);
                }
            });
        } else {
            if (filled($min)) {
                $query->where('scam_amount', '>=', $min);
            }
            if (filled($max)) {
                $query->where('scam_amount', '<=', $max);
            }
        }
    }
}
