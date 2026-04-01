<?php

namespace App\Constants;

use App\Traits\EnumSupport;

enum Permission: string
{
    use EnumSupport;

    case ADMIN_PANEL = 'admin_panel';

    // Permission
    case PERMISSION_LIST = 'permission:list';
    case PERMISSION_UPDATE = 'permission:update';

    // Role
    case ROLE_LIST = 'role:list';
    case ROLE_CREATE = 'role:create';
    case ROLE_UPDATE = 'role:update';
    case ROLE_DELETE = 'role:delete';

    // User
    case USER_LIST = 'user:list';
    case USER_CREATE = 'user:create';
    case USER_UPDATE = 'user:update';
    case USER_DELETE = 'user:delete';
    case LOGIN_AS_USER = 'login_as_user';

    // Customer
    case CUSTOMER_LIST = 'customer:list';
    case CUSTOMER_CREATE = 'customer:create';
    case CUSTOMER_UPDATE = 'customer:update';
    case CUSTOMER_DELETE = 'customer:delete';

    // Scam Leads
    case SCAM_LEAD_LIST = 'scam_lead:list';
    case SCAM_LEAD_CREATE = 'scam_lead:create';
    case SCAM_LEAD_UPDATE = 'scam_lead:update';
    case SCAM_LEAD_DELETE = 'scam_lead:delete';
    case SCAM_LEAD_TRANSFER = 'scam_lead:transfer';
    case SCAM_LEAD_BULK_DELETE = 'scam_lead_bulk_delete';
    case SCAM_LEAD_BULK_TRANSFER = 'scam_lead_bulk_transfer';

    // Scam
    case SCAM_LIST = 'scam:list';
    case SCAM_CREATE = 'scam:create';
    case SCAM_UPDATE = 'scam:update';
    case SCAM_DELETE = 'scam:delete';
    case SCAM_SALES_STATUS_REVIEW_SHOW = 'scam_sales_status_review:show';
    case SCAM_SALES_STATUS_REVIEW_UPDATE = 'scam_sales_status_review:update';
    case SCAM_DRAFTING_STATUS_REVIEW_SHOW = 'scam_drafting_status_review:show';
    case SCAM_DRAFTING_STATUS_REVIEW_UPDATE = 'scam_drafting_status_review:update';
    case SCAM_RECYCLE = 'scam_recycle';
    case SCAM_BULK_UPDATE = 'scam_bulk_update';
    case SCAM_RANDOM_ASSIGNER = 'scam_random_assigner';
    case STATUS_UNASSIGNED_SCAM_LIST = 'status_unassigned_scam:list';
    case DELETE_SCAM_STATUS_FILE = 'delete_scam_status_file';

    // Customer Enquiry
    case CUSTOMER_ENQUIRY_LIST = 'customer_enquiry:list';
    case CUSTOMER_ENQUIRY_DELETE = 'customer_enquiry:delete';
    case CUSTOMER_ENQUIRY_UPDATE_STATUS = 'customer_enquiry:update_status';

    // Scam Type
    case SCAM_TYPE_LIST = 'scam_type:list';
    case SCAM_TYPE_CREATE = 'scam_type:create';
    case SCAM_TYPE_UPDATE = 'scam_type:update';
    case SCAM_TYPE_DELETE = 'scam_type:delete';

    // Scam Status
    case SCAM_STATUS_LIST = 'scam_status:list';
    case SCAM_STATUS_CREATE = 'scam_status:create';
    case SCAM_STATUS_UPDATE = 'scam_status:update';
    case SCAM_STATUS_DELETE = 'scam_status:delete';
    case SCAM_STATUS_TRANSITION_SHOW = 'scam_status:transition_show';
    case SCAM_STATUS_TRANSITION_UPDATE = 'scam_status:transition_update';

    // Customer Enquiry Status
    case CUSTOMER_ENQUIRY_STATUS_LIST = 'customer_enquiry_status:list';
    case CUSTOMER_ENQUIRY_STATUS_CREATE = 'customer_enquiry_status:create';
    case CUSTOMER_ENQUIRY_STATUS_UPDATE = 'customer_enquiry_status:update';
    case CUSTOMER_ENQUIRY_STATUS_DELETE = 'customer_enquiry_status:delete';

    // Scam Source
    case SCAM_SOURCE_LIST = 'scam_source:list';
    case SCAM_SOURCE_CREATE = 'scam_source:create';
    case SCAM_SOURCE_UPDATE = 'scam_source:update';
    case SCAM_SOURCE_DELETE = 'scam_source:delete';

    // Scam Registration Amount
    case SCAM_REGISTRATION_AMOUNT_LIST = 'scam_registration_amount:list';
    case SCAM_REGISTRATION_AMOUNT_CREATE = 'scam_registration_amount:create';
    case SCAM_REGISTRATION_AMOUNT_UPDATE = 'scam_registration_amount:update';
    case SCAM_REGISTRATION_AMOUNT_DELETE = 'scam_registration_amount:delete';

    // Management

    case SALES_MANAGEMENT = 'sales_management';
    case SALES_MANAGEMENT_SELF = 'sales_management_self';
    case DRAFTING_MANAGEMENT = 'drafting_management';
    case DRAFTING_MANAGEMENT_SELF = 'drafting_management_self';
    case SERVICE_MANAGEMENT = 'service_management';
    case SERVICE_MANAGEMENT_SELF = 'service_management_self';

    // Escalations
    case ESCALATION_LIST = 'escalation:list';
    case ESCALATION_LIST_SELF = 'escalation_self:list';
    case ESCALATION_CREATE = 'escalation:create';
    case ESCALATION_DELETE = 'escalation:delete';

    // Notifications
    case NOTIFICATION_LIST = 'notification:list';
    case NOTIFICATION_LIST_SELF = 'notification_self:list';

    // Filters
    case SCAM_TYPE_FILTER = 'scam_type_filter';
    case SALES_ASSIGNEE_FILTER = 'sales_assignee_filter';
    case SALES_STATUS_FILTER = 'sales_status_filter';
    case DRAFTING_ASSIGNEE_FILTER = 'drafting_assignee_filter';
    case DRAFTING_STATUS_FILTER = 'drafting_status_filter';
    case SERVICE_ASSIGNEE_FILTER = 'service_assignee_filter';
    case SCAM_CREATED_AT_FILTER = 'scam_created_at_filter';
    case LAST_SALES_STATUS_UPDATED_AT_FILTER = 'last_sales_status_updated_at_filter';
    case LAST_DRAFTING_STATUS_UPDATED_AT_FILTER = 'last_drafting_status_updated_at_filter';
    case LAST_SERVICE_ASSIGNED_AT_FILTER = 'last_service_assigned_at_filter';
    case LAST_SALES_ASSIGNED_AT_FILTER = 'last_sales_assigned_at_filter';
    case LAST_DRAFTING_ASSIGNED_AT_FILTER = 'last_drafting_assigned_at_filter';

    // Scam Related Permissions
    case VIEW_SCAM_ASSIGNEE_LIST = 'view_scam_assignee_list';
    case VIEW_SCAM_LIFECYCLE = 'view_scam_lifecycle';
    case VIEW_RECYCLED_SCAM_LIFECYCLE = 'view_recyecled_scam_lifecycle';
    case VIEW_SCAM_CUSTOM_UPLOADED_FILES = 'view_scam_custom_uploaded_files';
    case VIEW_SCAM_STATUS_UPLOADED_FILES = 'view_scam_status_uploaded_files';

    // Report
    case REPORT_USER_SCAM_STATUS = 'report:user_scam_status';

    // User Related Permissions
    case VIEW_ALL_USERS_ACTIVITIES = 'view_all_users_activities';
    case VIEW_SELF_USERS_ACTIVITIES = 'view_self_users_activities';

    // Imports
    case SCAM_EXCEL_IMPORT = 'scam_excel_import';

    // Settings
    case LOGIN_SETTINGS = 'login_settings';
    case BUSINESS_SETTINGS = 'business_settings';
    case USER_PREFERENCES = 'user_preferences';

    // Customs
    case BYPASS_DISABLED_LOGIN = 'bypass_disabled_login';
    case PULSE_MONITOR = 'pulse_monitor';
    case PHPINFO = 'phpinfo';
    case LARAVEL_INFO = 'laravel_info';
    case TELESCOPE = 'telescope';

    case UPDATE_ALL_USERS_DETAILS = 'update_all_users_details';
    case CHANGE_ALL_USERS_PASSWORD = 'change_all_users_password';

    case UPDATE_LOCKED_SALES_STATUS = 'update_locked_sales_status';
    case UPDATE_LOCKED_DRAFTING_STATUS = 'update_locked_drafting_status';
    case SHOW_SCAM_SOURCE = 'show_scam_source';

    // Dashboard widgets

    case DASHBOARD_USER_STATS = 'dashboard:user_stats';
    case DASHBOARD_SCAM_STATS = 'dashboard:scam_stats';
    case DASHBOARD_TOTAL_SCAMS_CHART = 'dashboard:total_scams_chart';
    case DASHBOARD_SALES_STATUS_STATS = 'dashboard:sales_status_stats';
    case DASHBOARD_DRAFTING_STATUS_STATS = 'dashboard:drafting_status_stats';
    case DASHBOARD_CUSTOMERS_BY_REGION_CHART = 'dashboard:customers_by_region_chart';
    case DASHBOARD_SCAMS_BY_SOURCE_CHART = 'dashboard:scams_by_source_chart';
    case DASHBOARD_RECENT_SCAMS = 'dashboard:recent_scams';
    case SUB_ADMIN_MANAGEMENT = 'sub_admin_management';
    case SUB_ADMIN_FILTER = 'sub_admin_filter';

    // Logs

    case WHATSAPP_MESSAGE_LOGS = 'whatsapp_message_logs';

    // Blog
    case BLOG_LIST = 'blog:list';
    case BLOG_CREATE = 'blog:create';
    case BLOG_UPDATE = 'blog:update';
    case BLOG_DELETE = 'blog:delete';

    // App UI Data
    case APP_UI_DATA_LIST = 'app_ui_data:list';
    case APP_UI_DATA_CREATE = 'app_ui_data:create';
    case APP_UI_DATA_UPDATE = 'app_ui_data:update';
    case APP_UI_DATA_DELETE = 'app_ui_data:delete';
}
