<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permission;
use App\Models\WhatsappMessageLog;
use App\Services\LogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsappMessageLogController extends \App\Foundation\Controller
{
    /**
     * Constructor for WhatsappMessageLogController
     */
    public function __construct(
        protected LogService $service
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit(Permission::WHATSAPP_MESSAGE_LOGS, only: ['whatsappMessageLogs', 'show']),
        ];
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->service->whatsappMessageLogDatatable($request)->toJson();
        }

        return view('admin.whatsapp-message-logs.index');
    }

    public function show(WhatsappMessageLog $whatsappMessageLog): View
    {
        return view('admin.whatsapp-message-logs.show', [
            'log' => $whatsappMessageLog,
        ]);
    }
}
