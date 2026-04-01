<?php

namespace App\Http\Controllers\Admin;

class OfficeController extends \App\Foundation\Controller
{
    public function isOfficeTiming(): array
    {
        return ['value' => is_office_time()];
    }
}
