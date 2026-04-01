<?php

use App\Enums\ScamStatusType;
use App\Models\Scam;
use App\Models\ScamStatus;

// ── Inspect current state of scam 1115 only (no DB changes) ───────────────
$scam = Scam::find(1115);

if (!$scam) {
    echo "Scam #1115 not found.\n";
    exit(1);
}

$prefix          = ScamStatusType::SALES->value;
$statusUpdatedAt = $scam->{"{$prefix}_status_updated_at"};
$assigneeId      = $scam->{"{$prefix}_assignee_id"};
$statusId        = $scam->{"{$prefix}_status_id"};
$holdStatus      = ScamStatus::where('type', ScamStatusType::SALES)->where('name', 'HOLD')->first();

echo "SCAM #1115 — Current State\n";
echo "sales_assignee_id      : " . ($assigneeId ?? 'NULL') . "\n";
echo "sales_status_id        : " . ($statusId ?? 'NULL') . "\n";
echo "HOLD status id         : " . ($holdStatus?->id ?? 'NOT FOUND') . "\n";
echo "Is on HOLD status      : " . ($statusId == $holdStatus?->id ? "YES" : "NO") . "\n";
echo "sales_status_updated_at: " . ($statusUpdatedAt ?? 'NULL') . "\n";

if ($statusUpdatedAt) {
    $days = now()->diffInDays($statusUpdatedAt);
    echo "Days on HOLD           : {$days} days\n";

    if ($days >= 30) {
        echo "Status                 : WILL UNASSIGN (>= 30 days)\n";
    } elseif ($days >= 28) {
        echo "Status                 : REMINDER zone (28-29 days) — unassign in " . (30 - $days) . " day(s)\n";
    } else {
        echo "Status                 : Waiting — " . (30 - $days) . " day(s) until unassign, reminder in " . (28 - $days) . " day(s)\n";
    }
}
