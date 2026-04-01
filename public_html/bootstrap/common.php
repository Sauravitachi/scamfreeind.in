<?php

use App\Services\HelperService;
use Carbon\Carbon;

/**
 * Helper function to determine if the application is running in the production environment.
 *
 * @return bool Returns true if the application is in production mode, otherwise false.
 */
function is_production(): bool
{
    return (bool) app()->environment('production');
}

/**
 * Helper function to compare two arrays and check if they are equal.
 *
 * This function compares the content of both arrays, ensuring that both arrays contain
 * the same elements, regardless of their order.
 *
 * @param  array  $a  First array to compare.
 * @param  array  $b  Second array to compare.
 * @return bool Returns true if both arrays are equal, otherwise false.
 */
function array_equals(array $a, array $b): bool
{
    return empty(array_diff($a, $b)) && empty(array_diff($b, $a));
}

/**
 * Helper function to format a date based on the application's datetime format setting.
 *
 * This function accepts a date as either a Carbon instance or a string and formats it
 * according to the 'clean_datetime_format' setting in the application's configuration.
 *
 * @param  \Carbon\Carbon|string  $date  The date to format, either as a Carbon instance or a string.
 * @return string Returns the formatted date as a string.
 */
function format_date(\Carbon\Carbon|string|null $date): ?string
{
    if (! $date) {
        return null;
    }
    $cleanDateTimeFormat = config('settings.clean_datetime_format');
    if (is_string($date)) {
        return date($cleanDateTimeFormat, strtotime($date));
    }

    return $date->format($cleanDateTimeFormat);
}

/**
 * Helper function to create permissions for action methods.
 *
 * This function generates a middleware for checking permissions on specific controller actions.
 * It allows restricting actions based on the specified permission and provides options to
 * include or exclude certain actions.
 *
 * @param  \App\Constants\Permission|array  $permission  The permission enum to be checked.
 * @param  string|array<string>|null  $only  The actions to apply the permission check to (optional).
 * @param  string|array<string>|null  $except  The actions to exclude from the permission check (optional).
 * @return \Illuminate\Routing\Controllers\Middleware Returns a middleware object for permission handling.
 */
function permit(\App\Constants\Permission|array $permission, null|string|array $only = null, null|string|array $except = null): \Illuminate\Routing\Controllers\Middleware
{
    if (! is_array($permission)) {
        $permission = [$permission];
    }

    return new \Illuminate\Routing\Controllers\Middleware(
        \Spatie\Permission\Middleware\PermissionMiddleware::using(
            permission: array_map(fn (\App\Constants\Permission $pm) => $pm->value, $permission)
        ),
        only: $only,
        except: $except
    );
}

/**
 * Helper function to format a number into a shorter, human-readable form.
 *
 * This function converts large numbers into abbreviated formats (e.g., 1000 to 1K, 1000000 to 1M).
 * It works by dividing the number by 1000 iteratively until it's less than 1000 and appends
 * the appropriate unit (K, M, B, T).
 *
 * @param  float|int  $num  The number to be shortened.
 * @return string Returns the shortened number with up to two decimal places, followed by the appropriate unit.
 */
function short_amount($num): string
{
    $units = ['', 'K', 'M', 'B', 'T'];
    for ($i = 0; $num >= 1000; $i++) {
        $num /= 1000;
    }

    return round($num, 2).$units[$i];
}

/**
 * Helper function to format a monetary amount with optional abbreviation.
 *
 * This function formats a monetary amount, with options for displaying it in short format
 * (e.g., 1K for 1000) or in both full and short forms. It also trims trailing zeros
 * to make the formatted number cleaner.
 *
 * @param  string  $amount  The original amount as a string.
 * @param  bool  $shortForm  Set to true to display the amount in short format.
 * @param  bool  $both  Set to true to return both short and full formats in an array.
 * @return string|array Returns the formatted amount as a string by default. If $both is true, returns an array with both short and full formats.
 */
function format_amount(string $amount, bool $shortForm = false, bool $both = false): string|array
{
    // requires bcmath extension
    $max = 8;

    $number = bcmul($amount, '1', $max);

    // Remove trailing zeros and the decimal point
    $number = rtrim($number, '0');

    // appending 2 zeroes if all zeroes are trimmed
    if (substr($number, -1) === '.') {
        $number .= '00';
    }

    $shortNum = ($shortForm or $both) ? short_amount($number) : null;

    if ($both) {
        return ["₹$shortNum", "₹$number"];
    }

    return $shortNum ? "₹$shortNum" : "₹$number";
}

/**
 * Check if the HTTP request method is POST.
 *
 * This utility function verifies whether the given or current request
 * was made using the HTTP POST method. If no request object is provided,
 * it defaults to using Laravel's `request()` helper to fetch the current
 * HTTP request.
 *
 * @param  \Illuminate\Http\Request|null  $request  The request object to check. Defaults to the current request if null.
 * @return bool Returns true if the request method is POST; otherwise, false.
 */
function is_request_post(?\Illuminate\Http\Request $request = null): bool
{
    return ($request ?? request())->isMethod(\App\Constants\HttpMethod::POST->value);
}

/**
 * Retrieve the value of a specified application setting.
 *
 * @param  \App\Constants\Setting|array|string  $key  The setting key to retrieve.
 * @param  mixed|null  $default  The default value if the setting is not found. Defaults to `null`.
 * @return mixed The value of the setting, or the default value if not found.
 */
function setting(\App\Constants\Setting|array|string $key, $default = null)
{
    if (is_array($key)) {
        return \App\Services\SettingService::getInstance()->getMultiple($key);
    }

    return \App\Services\SettingService::getInstance()->get($key, $default);
}

/**
 * Generates a JavaScript validation script for a FormRequest
 * and binds it to a custom event on a specified element.
 *
 * @param  string  $formRequestClass  The FormRequest class name.
 * @param  string  $formSelector  The form selector (default: 'form').
 * @param  string  $eventTargetSelector  Selector for the element to bind the event.
 * @param  string  $event  The event name to trigger validation.
 * @return string The modified JavaScript validation script.
 */
function js_validation_custom_event(string $formRequestClass, string $formSelector, string $eventTargetSelector, string $event): string
{
    $script = \Proengsoft\JsValidation\Facades\JsValidatorFacade::formRequest(
        $formRequestClass,
        $formSelector
    )->render();

    return str_replace('jQuery(document).ready(function () {', "jQuery('{$eventTargetSelector}').on('{$event}', function () {", $script);
}

/**
 * Parses a date range string and returns a DatetimeRange object.
 *
 * If a separator (e.g., "to") is present, the function extracts and parses both start and end dates.
 * If only a single date is provided, it is used for both start and end.
 * Dates without a time component are set to the start or end of the day accordingly.
 *
 * @param  string  $dateRange  The date range string (e.g., "2024-01-01 to 2024-01-02" or "2024-01-01 12:30").
 * @param  string  $separator  The separator used to split the date range (default: "to").
 * @return \App\DTO\DatetimeRange A DTO containing the parsed start and end Carbon instances.
 */
function carbon_date_range(string $dateRange, string $separator = 'to', bool $expandDates = false): \App\DTO\DatetimeRange
{
    $parts = array_map('trim', explode($separator, $dateRange));
    $isSingleDate = count($parts) === 1;

    if (count($parts) === 2) {
        [$start, $end] = array_map(fn ($date) => \Carbon\Carbon::parse($date), $parts);
    } else {
        $start = \Carbon\Carbon::parse($dateRange);
        $end = \Carbon\Carbon::parse($dateRange);
    }

    if ($isSingleDate || $expandDates) {
        return new \App\DTO\DatetimeRange(start: $start->startOfDay(), end: $end->endOfDay());
    }

    return new \App\DTO\DatetimeRange(start: $start, end: $end);
}

/**
 * Checks if the given email is valid.
 *
 * This function uses PHP's built-in filter_var function with the FILTER_VALIDATE_EMAIL filter
 * to validate the format of the email.
 *
 * @param  string  $email  The email address to be validated.
 * @return bool Returns true if the email is valid, false otherwise.
 */
function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Trims whitespace from the beginning and end of a string and returns null if the result is an empty string.
 *
 * @param  string  $str  The input string to be trimmed.
 * @return string|null The trimmed string, or null if the trimmed string is empty.
 */
function trim_or_null(?string $str): ?string
{
    if ($str === null) {
        return null;
    }
    $trimmed = trim($str);

    return $trimmed === '' ? null : $trimmed;
}

/**
 * Determine the user type based on role permissions.
 *
 * This function checks the given role for specific permissions and returns
 * the corresponding user type. If no matching permissions are found,
 * it defaults to 'admin'.
 *
 * @param  \Spatie\Permission\Models\Role  $role  The role instance to check permissions for.
 * @return string The user type ('sales', 'drafting', or 'admin').
 */
function userType(\Spatie\Permission\Models\Role $role): string
{
    $type = 'admin';

    if (! $role->hasPermissionTo(\App\Constants\Permission::SALES_MANAGEMENT) && $role->hasPermissionTo(\App\Constants\Permission::SALES_MANAGEMENT_SELF)) {
        $type = 'sales';
    } elseif (! $role->hasPermissionTo(\App\Constants\Permission::DRAFTING_MANAGEMENT) && $role->hasPermissionTo(\App\Constants\Permission::DRAFTING_MANAGEMENT_SELF)) {
        $type = 'drafting';
    }

    return $type;
}

/**
 * Check if a given time (or current time by default) falls within the configured office timings.
 *
 * This function fetches the office start and end times from the HelperService
 * and checks if the provided time (or `now()` if none provided) is between those two times.
 * If office timings are not set or invalid, it defaults to returning true.
 *
 * @param  Carbon|null  $datetime  The time to check against office hours. Defaults to current time if null.
 * @return bool True if the time is within office hours or if office timings are not configured; false otherwise.
 */
function is_office_time(?Carbon $datetime = null): bool
{
    // Use provided time or fallback to current time.
    $targetDateTime = $datetime ?? now();

    // Retrieve office timings once per request.
    $officeTimings = once(fn () => HelperService::getInstance()->getOfficeTiming());

    if ($officeTimings === null) {
        return true;
    }

    // Check if the given time falls between office start and end times.
    return $targetDateTime->between($officeTimings[0], $officeTimings[1]);
}
