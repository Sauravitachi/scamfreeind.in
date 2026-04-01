<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CustomerEnquiryFilter
{
    public static function apply(Builder $query, Request $request)
    {
        $bypassed = $request->boolean('bypassed', false);
        // Filters

        $query->when(
            $request->filled('sales_assignee_id'),
            fn (Builder $q) => $q->whereSalesAssignee($request->input('sales_assignee_id'), $request->boolean('exclude_sales_assignee_id'))
        );

        $query->when(
            $request->filled('drafting_assignee_id'),
            fn (Builder $q) => $q->whereDraftingAssignee($request->input('drafting_assignee_id'), $request->boolean('exclude_drafting_assignee_id'))
        );

        $query->when(
            $request->filled('scam_amount_lb'),
            function (Builder $q) use ($request) {
                $q->whereHas('customer.scams', function (Builder $q) use ($request) {
                    $q->where('is_duplicate', 0)->whereNull('recycled_at')
                        ->where('scam_amount', '>=', $request->integer('scam_amount_lb'));
                });
            }
        );

        $query->when(
            $request->filled('scam_amount_ub'),
            function (Builder $q) use ($request) {
                $q->whereHas('customer.scams', function (Builder $q) use ($request) {
                    $q->where('is_duplicate', 0)->whereNull('recycled_at')
                        ->where('scam_amount', '<=', $request->integer('scam_amount_ub'));
                });
            }
        );

        /**
         * Sales Status Filter
         */
        if ($request->filled('sales_status_id')) {
            self::statusFilter(query: $query, request: $request, field: 'sales_status_id');
        }

        /**
         * Drafting Status Filter
         */
        if ($request->filled('drafting_status_id')) {
            self::statusFilter(query: $query, request: $request, field: 'drafting_status_id');
        }

        /**
         * Scam Sales Status Filter
         */
        if ($request->filled('scam_sales_status_id')) {
            self::scamStatusFilter(query: $query, request: $request, field: 'scam_sales_status_id', columnField: 'sales_status_id');
        }

        /**
         * Sales Status Updated at range filter
         */
        if ($request->filled('scam_sales_status_updated_at')) {
            self::scamDateRangeFilter(query: $query, request: $request, field: 'scam_sales_status_updated_at', columnField: 'sales_status_updated_at', columnFieldId: 'sales_assigned_at');
        }

        /**
         * Drafting Status Updated at range filter
         */
        if ($request->filled('scam_drafting_status_updated_at')) {
            self::scamDateRangeFilter(query: $query, request: $request, field: 'scam_drafting_status_updated_at', columnField: 'drafting_status_updated_at', columnFieldId: 'drafting_assigned_at');
        }

        /**
         * Drafting Sales Status Filter
         */
        if ($request->filled('scam_drafting_status_id')) {
            self::scamStatusFilter(query: $query, request: $request, field: 'scam_drafting_status_id', columnField: 'drafting_status_id');
        }

        $query->when($request->filled('scam_source_id'), fn (Builder $q) => $q->where('scam_source_id', $request->input('scam_source_id')));

        $query->when($request->filled('created_at'), function (Builder $q) use ($request) {
            $range = carbon_date_range($request->input('created_at'), 'to', expandDates: true);
            $q->whereBetween('created_at', [$range->start, $range->end]);
        });

        $query->when($request->filled('sales_status_updated_at'), function (Builder $q) use ($request) {
            $range = carbon_date_range($request->input('sales_status_updated_at'), 'to', expandDates: true);
            $q->whereBetween('sales_status_updated_at', [$range->start, $range->end]);
        });

        $query->when($request->filled('drafting_status_updated_at'), function (Builder $q) use ($request) {
            $range = carbon_date_range($request->input('drafting_status_updated_at'), 'to', expandDates: true);
            $q->whereBetween('drafting_status_updated_at', [$range->start, $range->end]);
        });
    }

    private static function statusFilter(Builder $query, Request $request, string $field): void
    {
        $keyData = $request->input($field);

        if (! is_array($keyData)) {
            $keyData = [$keyData];
        }

        if (! empty($keyData) && ! (count($keyData) == 1 && $keyData[0] === null)) {
            $query->where(function (Builder $q) use ($keyData, $field): void {
                in_array('-1', $keyData)
                    ? $q->whereNull($field)
                    : $q->whereIn($field, $keyData);
            });
        }
    }

    private static function scamStatusFilter(Builder $query, Request $request, string $field, string $columnField): void
    {
        $keyData = $request->input($field);

        if (! is_array($keyData)) {
            $keyData = [$keyData];
        }

        if (! empty($keyData) && ! (count($keyData) == 1 && $keyData[0] === null)) {
            $query->whereHas('customer.scams', function (Builder $q) use ($keyData, $columnField): void {
                $q->where('scams.is_duplicate', false)->where(function (Builder $q) use ($keyData, $columnField): void {
                    in_array('-1', $keyData)
                        ? $q->whereNull($columnField)
                        : $q->whereIn($columnField, $keyData);
                });
            });
        }
    }

    private static function scamDateRangeFilter(Builder $query, Request $request, string $field, string $columnField, ?string $columnFieldId = null): void
    {
        $key = $request->input($field);

        $range = carbon_date_range($key, 'to', expandDates: true);

        $query->whereHas('customer.scams', function (Builder $query) use ($columnField, $columnFieldId, $range) {
            if ($columnFieldId) {
                $query->where(function (Builder $q) use ($range, $columnField, $columnFieldId): void {
                    $q->whereNotNull("scams.{$columnFieldId}")->whereBetween("scams.{$columnField}", [$range->start, $range->end]);
                });
            } else {
                $query->whereBetween("scams.{$columnField}", [$range->start, $range->end]);
            }
        });
    }
}
