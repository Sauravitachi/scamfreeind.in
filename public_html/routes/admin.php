<?php

use App\Constants\HttpMethod;
use App\Constants\Permission;
use App\Enums\ScamStatusType;
use App\Http\Controllers\Admin\AccountSettingsController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerEnquiryController;
use App\Http\Controllers\Admin\CustomerEnquiryStatusController;
use App\Http\Controllers\Admin\EscalationChatController;
use App\Http\Controllers\Admin\EscalationController;
use App\Http\Controllers\Admin\FileUploadController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OfficeController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PreferenceController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ScamController;
use App\Http\Controllers\Admin\ScamLeadController;
use App\Http\Controllers\Admin\ScamRegistrationAmountController;
use App\Http\Controllers\Admin\ScamSourceController;
use App\Http\Controllers\Admin\StateController;
use App\Http\Controllers\Admin\ScamStatusController;
use App\Http\Controllers\Admin\ScamTypeController;
use App\Http\Controllers\Admin\ServerController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserActivityController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WhatsappMessageLogController;
use App\Http\Middleware\AdminAccessMiddleware;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;

$routes = function () {
    Route::controller(AuthController::class)->name('auth.')->group(function () {
        Route::middleware('guest')->group(function () {
            Route::get('login', 'login')->name('login');
            Route::post('login', 'handleLogin')->name('handle-login');
        });
        Route::post('logout', 'handleLogout')->name('handle-logout');
    });
    Route::middleware([
        'auth',
        AdminAccessMiddleware::class,
        PermissionMiddleware::using(Permission::ADMIN_PANEL->value),
    ])->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::resource('permissions', PermissionController::class)->except('create', 'store', 'destroy');
        Route::resource('roles', RoleController::class);
        Route::prefix('users')->as('users.')->controller(UserController::class)->group(function () {
            Route::post('{user}/status', 'changeStatus')->name('change-status');
            Route::post('{user}/change-password', 'changePassword')->name('change-password');
            Route::post('{user}/force-release-freeze', 'forceReleaseFreeze')->name('force-release-freeze');
            Route::post('{user}/login', 'loginAsUser')->name('login-as-user');
            Route::post('login-back-to-user', 'loginBackToUser')->name('login-back-to-user');
            Route::get('{user}/assignee-status', 'assigneeStatus')->name('assignee-status');
            Route::post('{user}/update-preference', [UserController::class, 'updatePreference'])->name('update-preference');
            Route::post('{user}/set-target', 'setTarget')->name('set-target');
        });
        Route::resource('users', UserController::class);
        Route::prefix('profile')->as('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'index'])->name('index');
        });
        Route::prefix('account-settings')->as('account-settings.')->group(function () {
            Route::get('/', [AccountSettingsController::class, 'index'])->name('index');
        });
        Route::prefix('customers')->as('customers.')->group(function () {
            Route::get('select-search', [CustomerController::class, 'selectSearch'])->name('select-search');
        });
        Route::resource('customers', CustomerController::class);
        Route::prefix('scams')->as('scams.')->controller(ScamController::class)->group(function () {
            Route::get('select-search', 'selectSearch')->name('select-search');
            Route::post('{scam}/assign-user', 'assignUser')->name('assign-user');
            Route::post('{scam}/change-state', 'changeState')->name('change-state');
            Route::get('{scam}/all-escalations', 'allScamEscalations')->name('all-scam-escalations');
            Route::post('{scam}/change-status', 'changeStatus')->name('change-status');
            Route::post('{scam}/change-scam-status-review', 'changeScamStatusReview')->name('change-scam-status-review');
            Route::post('{scam}/upload-scam-files', 'uploadScamFiles')->name('upload-scam-files');

            Route::post('bulk-assign-users', 'bulkAssignUsers')->name('bulk-assign-users');
            Route::post('process-import-file', 'processImportFile')->name('process-import-file');
            Route::post('scam/import', 'import')->name('import');
            Route::post('scam/bulk-recycle', 'bulkRecycle')->name('bulk-recycle');
            Route::post('scam/bulk-update', 'bulkUpdate')->name('bulk-update');
            Route::get('update-status-data-form/{scam}/{scam_status}', 'statusDataForm')->name('update-status-data-form');
            Route::post('update-status-data/{scam}/{scam_status}', 'updateStatusData')->name('update-status-data');
            Route::post('random-scam-assign', 'randomScamAssign')->name('random-scam-assign');
            Route::delete('delete-scam-status-file/{scam_status_file}', 'deleteStatusFile')->name('delete-scam-status-file');
            Route::delete('delete-scam-file/{scam_file}', 'deleteScamFile')->name('delete-scam-file');
            Route::post('acknowledge-status-reminders', 'acknowledgeStatusReminders')->name('acknowledge-status-reminders');
            Route::match(['get', 'put', 'patch', 'post'], '{scam}/remark', 'updateRemark')->name('update-remark');
            Route::post('bulk-excel-export', 'bulkExcelExport')->name('bulk-excel-export');
        });
        Route::resource('scams', ScamController::class);
        Route::prefix('customer-enquiries')->controller(CustomerEnquiryController::class)->as('customer-enquiries.')->group(function () {
            Route::post('{customer_enquiry}/change-status', 'changeStatus')->name('change-status');
        });
        Route::resource('customer-enquiries', CustomerEnquiryController::class)->only('index', 'show', 'destroy');
        Route::prefix('scam-leads')->as('scam-leads.')->controller(ScamLeadController::class)->group(function () {
            Route::post('{scam_lead}/transfer', 'transfer')->name('transfer');
            Route::get('{scam_lead}/similar-leads', 'similarLeads')->name('similar-leads');
            Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete');
            Route::post('bulk-transfer', 'bulkTransfer')->name('bulk-transfer');
        });
        Route::resource('scam-leads', ScamLeadController::class);
        Route::prefix('escalations')->as('escalations.')->controller(EscalationController::class)->group(function () {
            Route::post('{escalation}/reject', 'reject')->name('reject');
            Route::post('{escalation}/close', 'close')->name('close');
        });
        Route::resource('escalations', EscalationController::class);
        Route::post('escalation-chats/{escalation}', [EscalationChatController::class, 'store'])->name('escalation-chats.store');
        Route::prefix('notifications')->as('notifications.')->controller(NotificationController::class)->group(function () {
            Route::get('unread-notifications', 'unreadNotifications')->name('unread-notifications');
            Route::get('unread-notifications-count', 'unreadNotificationsCount')->name('unread-notifications-count');
            Route::post('mark-latest-read', 'markLatestNotificationAsRead')->name('mark-latest-read');
        });
        Route::resource('notifications', NotificationController::class)->only('index', 'show');
        Route::resource('whatsapp-message-logs', WhatsappMessageLogController::class)->only('index', 'show');
        Route::resource('user-activities', UserActivityController::class)->only('index');
        Route::prefix('reports')->as('reports.')->controller(ReportController::class)->group(function () {
            Route::match([HttpMethod::GET->value, HttpMethod::POST->value], 'user-case', 'userCaseStatusReport')->name('user-case-report');
            Route::get('scam-status-transition', 'scamStatusTransitionReport')->name('scam-status-transition-report');
        });
        Route::prefix('preferences')->as('preferences.')->controller(PreferenceController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('/', 'update')->name('update');
        });
        Route::prefix('settings')->as('settings.')->controller(SettingsController::class)->group(function () {
            Route::match([HttpMethod::GET->value, HttpMethod::POST->value], 'login', 'login')->name('login');
            Route::get('business', 'business')->name('business');
            Route::post('business', 'updateBusinessSetting')->name('update-business.settings');
        });
        Route::resource('scam-types', ScamTypeController::class);
        Route::prefix('scam-statuses')->name('scam-statuses.')->controller(ScamStatusController::class)->group(function () {
            Route::get('{type}/transitions', 'transition')->name('transition')->whereIn('type', ScamStatusType::array());
            Route::post('{type}/transitions/handle', 'handleTransition')->name('handle-transition')->whereIn('type', ScamStatusType::array());
        });
        Route::resource('scam-statuses', ScamStatusController::class);
        Route::resource('customer-enquiry-statuses', CustomerEnquiryStatusController::class);
        Route::prefix('scam-sources')->as('scam-sources.')->controller(ScamSourceController::class)->group(function () {
            Route::get('select-search', 'selectSearch')->name('select-search');
        });
        Route::resource('scam-sources', ScamSourceController::class);
        Route::prefix('states')->as('states.')->controller(StateController::class)->group(function () {
            Route::get('select-search', 'selectSearch')->name('select-search');
        });
        Route::resource('states', StateController::class);
        Route::post('upload-file', [FileUploadController::class, 'store'])->name('upload-file');
        Route::prefix('server')->name('server.')->controller(ServerController::class)->group(function () {
            Route::get('phpinfo', 'phpinfo')->name('phpinfo');
            Route::get('laravel-info', 'laravelInfo')->name('laravel-info');
        });
        Route::prefix('scam-registration-amounts')->as('scam-registration-amounts.')->controller(ScamRegistrationAmountController::class)->group(function () {
            Route::post('{scam_registration_amount}/status', 'changeStatus')->name('change-status');
        });
        Route::resource('scam-registration-amounts', ScamRegistrationAmountController::class);
        Route::prefix('office')->as('office.')->controller(OfficeController::class)->group(function () {
            Route::get('is-office-timing', 'isOfficeTiming')->name('is-office-timing');
        });
        Route::resource('blog', BlogController::class);
        Route::resource('app-ui-data', \App\Http\Controllers\Admin\AppUiDataController::class);
        Route::get('sub_admin', [ScamController::class, 'subAdmin'])->name('sub_admin');
    });
};

if (app()->environment('production')) {
    Route::domain('scamfreeindia.com')->name('admin.')->group($routes);
} else {
    Route::prefix('admin')->name('admin.')->group($routes);
}
