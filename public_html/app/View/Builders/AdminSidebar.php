<?php

namespace App\View\Builders;

use App\Constants\Permission;
use App\Foundation\View\Builders\Sidebar;

class AdminSidebar extends Sidebar
{
    protected function handle(): void
    {
        $userRole = $this->user->getRoleString();

        // Dashboard
        $this->add('dashboard', 'Dashboard', 'ti ti-home', route('admin.home'));

        // User Management
        if (
            $this->user->canAny([
                Permission::ROLE_LIST->value,
                Permission::PERMISSION_LIST->value,
                Permission::USER_LIST->value,
                Permission::VIEW_ALL_USERS_ACTIVITIES->value,
                Permission::VIEW_SELF_USERS_ACTIVITIES->value,
            ])
        ) {

            $this->add(name: 'user_mgmt', title: 'Manage Users', icon: 'ti ti-user-circle', url: null);

            if ($this->user->hasPermissionTo(Permission::ROLE_LIST->value)) {
                $this->addSubmenu(title: 'Roles', url: route('admin.roles.index'));
            }

            if ($this->user->hasPermissionTo(Permission::PERMISSION_LIST->value)) {
                $this->addSubmenu(title: 'Permissions', url: route('admin.permissions.index'));
            }

            if ($this->user->hasPermissionTo(Permission::USER_LIST->value)) {
                $this->addSubmenu(title: 'Users', url: route('admin.users.index'));
            }

            if ($this->user->canAny([Permission::VIEW_ALL_USERS_ACTIVITIES, Permission::VIEW_SELF_USERS_ACTIVITIES])) {
                $this->addSubmenu(title: 'User Activities', url: route('admin.user-activities.index'));
            }
        }

        // Scam Management
        if (
            $this->user->canAny([
                Permission::CUSTOMER_LIST->value,
                Permission::SCAM_LIST->value,
                Permission::ESCALATION_LIST->value,
                Permission::ESCALATION_LIST_SELF->value,
            ])
        ) {

            $this->add(name: 'scam_mgmt', title: 'Manage Scams', icon: 'ti ti-users-group', url: null);

            if ($this->user->hasPermissionTo(Permission::CUSTOMER_LIST->value)) {
                $this->addSubmenu(title: 'Customers', url: route('admin.customers.index'));
            }

            if ($this->user->hasPermissionTo(Permission::SCAM_LIST->value)) {
                $this->addSubmenu(title: 'Scams', url: route('admin.scams.index'));
            }

            if ($this->user->hasPermissionTo(Permission::SCAM_LEAD_LIST->value)) {
                $this->addSubmenu(title: 'Scam Leads', url: route('admin.scam-leads.index'));
            }

        if ($this->user->hasPermissionTo(Permission::CUSTOMER_ENQUIRY_LIST->value)) {

            $isSales = $userRole === 'sales' || \App\Models\CustomerEnquiry::whereSalesAssignee($this->user->id)->exists();
            $isDrafting = $userRole === 'drafting' || \App\Models\CustomerEnquiry::whereDraftingAssignee($this->user->id)->exists();

            if ($userRole === 'admin') {
                $isSales = true;
                $isDrafting = true;
            }

            if ($isSales) {
                $this->addSubmenu(title: 'Sales Enquiries', url: route('admin.customer-enquiries.index', ['type' => 'sales']));
            }

            if ($isDrafting) {
                $this->addSubmenu(title: 'Drafting Escalations', url: route('admin.customer-enquiries.index', ['type' => 'drafting']));
            }
        }

            if ($this->user->canAny([Permission::ESCALATION_LIST->value, Permission::ESCALATION_LIST_SELF->value])) {
                $this->addSubmenu(title: 'Internal Escalations', url: route('admin.escalations.index'));
            }
        }

        // Reports
        if (
            $this->user->canAny([
                Permission::REPORT_USER_SCAM_STATUS,
            ])
        ) {

            $this->add(name: 'reports', title: 'Reports', icon: 'ti ti-report', url: null);

            if ($this->user->hasPermissionTo(Permission::REPORT_USER_SCAM_STATUS)) {
                $this->addSubmenu(title: 'User Case Report', url: route('admin.reports.user-case-report'));
            }
        }

        // Master
        if (
            $this->user->canAny([
                Permission::SCAM_TYPE_LIST->value,
                Permission::SCAM_STATUS_LIST->value,
                Permission::SCAM_SOURCE_LIST->value,
                Permission::CUSTOMER_ENQUIRY_STATUS_LIST->value,
                Permission::SCAM_REGISTRATION_AMOUNT_LIST->value,
            ])
        ) {
            $this->add(name: 'master', title: 'Master', icon: 'ti ti-brand-envato', url: null);

            if ($this->user->can(Permission::SCAM_TYPE_LIST->value)) {
                $this->addSubmenu(title: 'Scam Type', url: route('admin.scam-types.index'));
            }

            if ($this->user->can(Permission::SCAM_STATUS_LIST->value)) {
                $this->addSubmenu(title: 'Scam Status', url: route('admin.scam-statuses.index'));
            }

            if ($this->user->can(Permission::CUSTOMER_ENQUIRY_STATUS_LIST->value)) {
                $this->addSubmenu(title: 'Customer Enquiry Status', url: route('admin.customer-enquiry-statuses.index'));
            }

            if ($this->user->can(Permission::SCAM_SOURCE_LIST->value)) {
                $this->addSubmenu(title: 'Scam Source', url: route('admin.scam-sources.index'));
            }

            if ($this->user->can(Permission::SCAM_REGISTRATION_AMOUNT_LIST->value)) {
                $this->addSubmenu(title: 'Scam Registration Amounts', url: route('admin.scam-registration-amounts.index'));
            }
        }

        // Notifications
        if (
            $this->user->canAny([
                Permission::NOTIFICATION_LIST->value,
                Permission::NOTIFICATION_LIST_SELF->value,
            ])
        ) {
            $this->add(name: 'notifications', title: 'Notifications', icon: 'ti ti-bell', url: route('admin.notifications.index'));
        }

        // Logs
        if (
            $this->user->canAny([
                Permission::WHATSAPP_MESSAGE_LOGS->value,
            ])
        ) {
            $this->add(name: 'logs', title: 'Logs', icon: 'ti ti-table', url: null);

            if ($this->user->can(Permission::WHATSAPP_MESSAGE_LOGS->value)) {
                $this->addSubmenu(title: 'Whatsapp Message Logs', url: route('admin.whatsapp-message-logs.index'));
            }

        }

        // Settings
        if (
            $this->user->canAny([
                Permission::LOGIN_SETTINGS->value,
                Permission::USER_PREFERENCES->value,
            ])
        ) {
            $this->add(name: 'settings', title: 'Settings', icon: 'ti ti-settings', url: null);

            if ($this->user->can(Permission::USER_PREFERENCES->value)) {
                $this->addSubmenu(title: 'User Preferences', url: route('admin.preferences.index'));
            }

            if ($this->user->can(Permission::LOGIN_SETTINGS->value)) {
                $this->addSubmenu(title: 'Login Settings', url: route('admin.settings.login'));
            }

            if ($this->user->can(Permission::BUSINESS_SETTINGS->value)) {
                $this->addSubmenu(title: 'Business Settings', url: route('admin.settings.business'));
            }
        }

        // Server Management
        if (
            $this->user->canAny([
                Permission::PULSE_MONITOR->value,
                Permission::TELESCOPE->value,
                Permission::PHPINFO->value,
            ])
        ) {
            $this->add(name: 'server', title: 'Server', icon: 'ti ti-server', url: null);

            if ($this->user->can(Permission::PULSE_MONITOR->value)) {
                $this->addSubmenu(title: 'Pulse Monitor', url: route('pulse'));
            }

            // if ($this->user->can(Permission::TELESCOPE->value)) {
            //     $this->addSubmenu(title: 'Telescope', url: route('telescope'));
            // }

            if ($this->user->can(Permission::PHPINFO->value)) {
                $this->addSubmenu(title: 'PHP Info', url: route('admin.server.phpinfo'));
            }

            if ($this->user->can(Permission::LARAVEL_INFO->value)) {
                $this->addSubmenu(title: 'Laravel Info', url: route('admin.server.laravel-info'));
            }
        }

        {
            $this->add(name: 'sub_admin', title: 'Sub Admin', icon: 'ti ti-user', url: null);

            if ($this->user->can(Permission::SUB_ADMIN_MANAGEMENT->value)) {
                $this->addSubmenu(title: 'Sub Admin', url: route('admin.sub_admin'));
            }
        }
        {
            $this->add(name: 'blog', title: 'Blog', icon: 'ti ti-news', url: null);

            if ($this->user->can(Permission::BLOG_LIST->value)) {
                $this->addSubmenu(title: 'Blog', url: route('admin.blog.index'));
            }
        }

        {
            $this->add(name: 'ui_data', title: 'UI Data', icon: 'ti ti-layout-sidebar', url: null);

            if ($this->user->can(Permission::APP_UI_DATA_LIST->value)) {
                $this->addSubmenu(title: 'App UI Data', url: route('admin.app-ui-data.index'));
            }
        }

    }
}
