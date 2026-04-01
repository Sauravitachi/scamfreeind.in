<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

if (! class_exists('Bc')) {
    /**
     * @method static void resource(string $name, string $pluralTitle, ?string $singularTitle = null)
     */
    class Bc extends Breadcrumbs
    {
        // No need to implement anything, this just provides IDE support for new macro named 'resource'
    }
}

Bc::macro('resource', function (string $name, string $pluralTitle, ?string $singularTitle = null) {
    Bc::for("admin.$name.index", function ($trail) use ($name, $pluralTitle) {
        $trail->parent('admin.dashboard');
        $trail->push($pluralTitle, route("admin.$name.index"));
    });

    Bc::for("admin.$name.create", function ($trail) use ($name, $pluralTitle, $singularTitle) {
        $trail->parent("admin.$name.index");
        $trail->push('Add New '.($singularTitle ?? $pluralTitle), route("admin.$name.create"));
    });

    Bc::for("admin.$name.edit", function ($trail) use ($name, $pluralTitle, $singularTitle) {
        $trail->parent("admin.$name.index");
        $trail->push('Edit '.($singularTitle ?? $pluralTitle));
    });

    Bc::for("admin.$name.show", function ($trail) use ($name) {
        $trail->parent("admin.$name.index");
        $trail->push('Details');
    });
});

Bc::for('admin.dashboard', function (BreadcrumbTrail $trail) {
    $trail->push('Dashboard', route('admin.home'));
});

Bc::for('admin.profile.index', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Profile');
});

Bc::for('admin.account-settings.index', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Account Settings');
});

Bc::resource(name: 'roles', pluralTitle: 'Roles', singularTitle: 'Role');
Bc::resource(name: 'permissions', pluralTitle: 'Permissions', singularTitle: 'Permission');
Bc::resource(name: 'users', pluralTitle: 'Users', singularTitle: 'User');
Bc::resource(name: 'scam-types', pluralTitle: 'Scam Types', singularTitle: 'Scam Type');
Bc::resource(name: 'scam-statuses', pluralTitle: 'Scam Statuses', singularTitle: 'Scam Status');
Bc::resource(name: 'customer-enquiry-statuses', pluralTitle: 'Customer Enquiry Statuses', singularTitle: 'Customer Enquiry Status');
Bc::resource(name: 'scam-sources', pluralTitle: 'Scam Sources', singularTitle: 'Scam Source');
Bc::resource(name: 'scam-registration-amounts', pluralTitle: 'Scam Registration Amounts', singularTitle: 'Scam Registration Amount');
Bc::resource(name: 'customers', pluralTitle: 'Customers', singularTitle: 'Customer');
Bc::resource(name: 'scams', pluralTitle: 'Scams', singularTitle: 'Scam');
Bc::resource(name: 'customer-enquiries', pluralTitle: 'Customer Enquiries', singularTitle: 'Customer Enquiry');
Bc::resource(name: 'scam-leads', pluralTitle: 'Scam Leads', singularTitle: 'Scam Lead');
Bc::resource(name: 'escalations', pluralTitle: 'Escalations', singularTitle: 'Escalation');
Bc::resource(name: 'notifications', pluralTitle: 'Notifications', singularTitle: 'Notification');
Bc::resource(name: 'user-activities', pluralTitle: 'User Activities', singularTitle: 'User Activity');
Bc::resource(name: 'blog', pluralTitle: 'Blogs', singularTitle: 'Blog');
Bc::resource(name: 'app-ui-data', pluralTitle: 'App UI Datas', singularTitle: 'App UI Data');
Bc::resource('whatsapp-message-logs', pluralTitle: 'Whatsapp Message Logs', singularTitle: 'Whatsapp Message Log');

Bc::for('admin.reports.user-case-report', function (BreadcrumbTrail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('User Case Report');
});

Bc::for('admin.preferences.index', function (BreadcrumbTrail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('User Preferences');
});

Bc::for('admin.settings.login', function (BreadcrumbTrail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Login Settings');
});

Bc::for('admin.settings.business', function (BreadcrumbTrail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Business Settings');
});

BC::for('admin.scam-statuses.transition', function (BreadcrumbTrail $trail): void {
    $trail->parent('admin.scam-statuses.index');
    $trail->push('Status Transition');
});

BC::for('admin.server.laravel-info', function (BreadcrumbTrail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Server')->push('Laravel Info');
});

BC::for('admin.sub_admin', function (BreadcrumbTrail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Sub Admin', route('admin.sub_admin'));
});
