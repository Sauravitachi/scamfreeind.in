<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = Carbon::now();

        // Insert only if they don't already exist (idempotent)
        $existing = DB::table('permissions')
            ->whereIn('name', ['sub_admin_management', 'sub_admin_management_self'])
            ->pluck('name')
            ->toArray();

        $toInsert = [];

        if (!in_array('sub_admin_management', $existing)) {
            $toInsert[] = [
                'name'       => 'sub_admin_management',
                'label'      => 'Sub Admin Management',
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!in_array('sub_admin_management_self', $existing)) {
            $toInsert[] = [
                'name'       => 'sub_admin_management_self',
                'label'      => 'Sub Admin Management Self',
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($toInsert)) {
            DB::table('permissions')->insert($toInsert);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')
            ->whereIn('name', ['sub_admin_management', 'sub_admin_management_self'])
            ->delete();
    }
};
