<?php

namespace App\Constants;

use App\Traits\EnumSupport;

enum FileDirectory: string
{
    use EnumSupport;

    case ESCALATIONS = 'escalations';
    case STATUS_FILES = 'status-files';
    case SCAM_IMPORT_FILES = 'scam-import-files';
    case SCAM_FILES = 'scam-files';
}
