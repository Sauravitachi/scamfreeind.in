<?php

namespace App\Services;

use App\Models\WhatsappMessageLog;
use Illuminate\Http\Request;
use Yajra\DataTables\EloquentDataTable;

class LogService extends Service
{
    public function whatsappMessageLogDatatable(Request $request): EloquentDataTable
    {
        $query = WhatsappMessageLog::query()->with('recipient')
            ->select(['id', 'whatsapp_number', 'recipient_type', 'recipient_id', 'template_name', 'broadcast_name', 'response_status_code', 'created_at']);

        $table = datatables()->eloquent($query);

        $table->addColumn('recipient_detail', function (WhatsappMessageLog $log) {
            if ($log->recipient) {
                $type = $log->recipientEntityType;
                $data = $log->recipient ? $log->recipient->getUserDetailText() : null;

                return compact('type', 'data');
            }

            return null;
        });

        $table->editColumn('created_at', fn (WhatsappMessageLog $log): ?string => format_date($log->created_at));

        return $table;
    }
}
