<?php

namespace App\Exports;

use App\Models\Scam;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ScamExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected Collection $scams;

    /**
     * Pass collection from controller
     */
    public function __construct(Collection $scams)
    {
        $this->scams = $scams;
    }

    /**
     * Data source
     */
    public function collection()
    {
        return $this->scams;
    }

    /**
     * Excel headings
     */
    public function headings(): array
    {
        return [
            'Track ID',
            'Customer Name',
            'Phone Number',
            'Scam Type',
            'Scam Amount',
            'Source',
            'Sales Assignee',
            'Sales Status',
            'Drafting Assignee',
            'Drafting Status',
            'Service Assignee',
            'Created Date',
            'Remark',
        ];
    }

    /**
     * Map each row
     */
    public function map($scam): array
    {
        return [
            $scam->track_id,
            $scam->customer->fullName ?? '',
            $scam->customer->fullPhoneNumber ?? '',
            optional($scam->scamType)->title,
            $scam->scam_amount,
            optional($scam->scamSource)->title,

            optional($scam->salesAssignee)->name,
            optional($scam->salesStatus)->title,

            optional($scam->draftingAssignee)->name,
            optional($scam->draftingStatus)->title,

            optional($scam->serviceAssignee)->name,

            optional($scam->created_at)?->format('d-m-Y'),
            $scam->remark,
        ];
    }
}
