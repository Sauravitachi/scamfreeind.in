<?php

namespace App\Constants;

use App\Traits\EnumSupport;

enum ActivityEvent: string
{
    use EnumSupport;

    case LOGIN = 'login';
    case FAILED_LOGIN_ATTEMPT = 'failed_login_attempt';
    case LOGOUT = 'logout';
    case UNAUTHORIZED_ACCESS = 'unauthorized_access';
    case VISITED = 'visited';
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case UPLOADED = 'uploaded';
    case SETTINGS_CHANGED = 'settings_changed';
    case SCAM_ASSIGN_SALES = 'scam_assign_sales';
    case SCAM_ASSIGN_DRAFTING = 'scam_assign_drafting';
    case SCAM_ASSIGN_SERVICE = 'scam_assign_service';
    case SCAM_ASSIGN_SUB_ADMIN = 'scam_assign_sub_admin';
    case SCAM_BULK_ASSIGN_AND_UPDATE = 'scam_bulk_assign_and_update';
    case RANDOM_SCAM_ASSIGN = 'random_scam_assign';
    case SCAM_BULK_UPDATE = 'scam_bulk_update';
    case SCAM_BULK_RECYCLE = 'scam_bulk_recycle';
    case SCAM_STATUS_REVIEW = 'scam_status_review';
    case BULK_DELETED = 'bulk_deleted';
    case BULK_TRANSFER = 'bulk_transfer';
    case SCAM_IMPORT = 'scam_import';
    case TRANSFERRED = 'transferred';
    case LOGIN_AS_USER = 'login_as_user';

    public function label(): string
    {
        return match ($this) {
            self::LOGIN => 'User Logged In',
            self::FAILED_LOGIN_ATTEMPT => 'Failed Login Attempt',
            self::LOGOUT => 'User Logged Out',
            self::UNAUTHORIZED_ACCESS => 'Unauthorized Access',
            self::VISITED => 'Page Visit',
            self::CREATED => 'Resource Created',
            self::UPDATED => 'Resource Updated',
            self::DELETED => 'Resource Deleted',
            self::UPLOADED => 'Uploaded',
            self::SETTINGS_CHANGED => 'Settings Changed',
            self::SCAM_ASSIGN_SALES => 'Scam Assign Sales',
            self::SCAM_ASSIGN_DRAFTING => 'Scam Assign Drafting',
            self::SCAM_ASSIGN_SERVICE => 'Scam Assign Service',
            self::SCAM_ASSIGN_SUB_ADMIN => 'Scam Assign Sub Admin',
            self::SCAM_BULK_ASSIGN_AND_UPDATE => 'Scam Bulk Assign And Update',
            self::RANDOM_SCAM_ASSIGN => 'Random Scam Assign',
            self::SCAM_BULK_UPDATE => 'Scam Bulk Update',
            self::SCAM_BULK_RECYCLE => 'Scam Bulk Recycle',
            self::SCAM_STATUS_REVIEW => 'Scam Status Review',
            self::BULK_DELETED => 'Bulk Deleted',
            self::BULK_TRANSFER => 'Bulk Transfer',
            self::SCAM_IMPORT => 'Scam Import',
            self::TRANSFERRED => 'Transferred',
            self::LOGIN_AS_USER => 'Login As User'
        };
    }
}
