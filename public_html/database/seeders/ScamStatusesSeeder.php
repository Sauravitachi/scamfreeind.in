<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScamStatusesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $statuses = [

            /* ================= SALES ================= */

            [ 'id'=>1,'index'=>1,'slug'=>'sales_assigned','title'=>'Assigned','type'=>'sales','notify_after_days'=>null,'remainder_after_hours'=>0,'customer_enquiry_notify_role_id'=>null,'cap_scams'=>null,'cap_last_days'=>null,'is_file_required'=>0,'is_data_update_required'=>1,'is_scam_type_update_required'=>0,'is_lock'=>0,'is_approval_required'=>0,'bypass_enquiry'=>0,'is_closed'=>0,'is_freezable'=>1,'unassign_scam'=>0,'hours_to_freeze'=>null,'freeze_scams_threshold'=>null,'freeze_release_scams_threshold'=>null,'unassign_scam_in_days'=>1,'created_at'=>$now,'updated_at'=>$now ],

            [ 'id'=>2,'index'=>2,'slug'=>'opened','title'=>'Opened','type'=>'sales','notify_after_days'=>null,'remainder_after_hours'=>0,'customer_enquiry_notify_role_id'=>null,'cap_scams'=>null,'cap_last_days'=>null,'is_file_required'=>0,'is_data_update_required'=>0,'is_scam_type_update_required'=>0,'is_lock'=>0,'is_approval_required'=>0,'bypass_enquiry'=>0,'is_closed'=>0,'is_freezable'=>1,'unassign_scam'=>0,'hours_to_freeze'=>null,'freeze_scams_threshold'=>null,'freeze_release_scams_threshold'=>null,'unassign_scam_in_days'=>4,'created_at'=>$now,'updated_at'=>$now ],

            [ 'id'=>3,'index'=>3,'slug'=>'ringing','title'=>'Ringing','type'=>'sales','notify_after_days'=>null,'remainder_after_hours'=>0,'customer_enquiry_notify_role_id'=>null,'cap_scams'=>null,'cap_last_days'=>null,'is_file_required'=>0,'is_data_update_required'=>0,'is_scam_type_update_required'=>0,'is_lock'=>0,'is_approval_required'=>0,'bypass_enquiry'=>0,'is_closed'=>0,'is_freezable'=>1,'unassign_scam'=>0,'hours_to_freeze'=>null,'freeze_scams_threshold'=>null,'freeze_release_scams_threshold'=>null,'unassign_scam_in_days'=>4,'created_at'=>$now,'updated_at'=>$now ],

            [ 'id'=>5,'index'=>5,'slug'=>'call_back','title'=>'Call Back','type'=>'sales','notify_after_days'=>null,'remainder_after_hours'=>0,'customer_enquiry_notify_role_id'=>null,'cap_scams'=>null,'cap_last_days'=>null,'is_file_required'=>0,'is_data_update_required'=>0,'is_scam_type_update_required'=>0,'is_lock'=>0,'is_approval_required'=>0,'bypass_enquiry'=>0,'is_closed'=>0,'is_freezable'=>1,'unassign_scam'=>0,'hours_to_freeze'=>null,'freeze_scams_threshold'=>null,'freeze_release_scams_threshold'=>null,'unassign_scam_in_days'=>7,'created_at'=>$now,'updated_at'=>$now ],

            [ 'id'=>6,'index'=>6,'slug'=>'registered','title'=>'Registered','type'=>'sales','notify_after_days'=>null,'remainder_after_hours'=>0,'customer_enquiry_notify_role_id'=>6,'cap_scams'=>null,'cap_last_days'=>null,'is_file_required'=>1,'is_data_update_required'=>1,'is_scam_type_update_required'=>1,'is_lock'=>0,'is_approval_required'=>1,'bypass_enquiry'=>0,'is_closed'=>0,'is_freezable'=>0,'unassign_scam'=>0,'hours_to_freeze'=>null,'freeze_scams_threshold'=>null,'freeze_release_scams_threshold'=>null,'unassign_scam_in_days'=>null,'created_at'=>$now,'updated_at'=>$now ],

            [ 'id'=>7,'index'=>7,'slug'=>'not_interested','title'=>'Not Interested','type'=>'sales','notify_after_days'=>null,'remainder_after_hours'=>0,'customer_enquiry_notify_role_id'=>null,'cap_scams'=>null,'cap_last_days'=>null,'is_file_required'=>0,'is_data_update_required'=>0,'is_scam_type_update_required'=>0,'is_lock'=>0,'is_approval_required'=>0,'bypass_enquiry'=>0,'is_closed'=>0,'is_freezable'=>1,'unassign_scam'=>0,'hours_to_freeze'=>null,'freeze_scams_threshold'=>null,'freeze_release_scams_threshold'=>null,'unassign_scam_in_days'=>null,'created_at'=>$now,'updated_at'=>$now ],

            [ 'id'=>9,'index'=>9,'slug'=>'not_available','title'=>'Not Available','type'=>'sales','notify_after_days'=>null,'remainder_after_hours'=>0,'customer_enquiry_notify_role_id'=>null,'cap_scams'=>null,'cap_last_days'=>null,'is_file_required'=>0,'is_data_update_required'=>0,'is_scam_type_update_required'=>0,'is_lock'=>0,'is_approval_required'=>0,'bypass_enquiry'=>0,'is_closed'=>0,'is_freezable'=>1,'unassign_scam'=>0,'hours_to_freeze'=>null,'freeze_scams_threshold'=>null,'freeze_release_scams_threshold'=>null,'unassign_scam_in_days'=>2,'created_at'=>$now,'updated_at'=>$now ],

            [ 'id'=>10,'index'=>10,'slug'=>'hold','title'=>'Hold','type'=>'sales','notify_after_days'=>null,'remainder_after_hours'=>0,'customer_enquiry_notify_role_id'=>null,'cap_scams'=>null,'cap_last_days'=>null,'is_file_required'=>0,'is_data_update_required'=>0,'is_scam_type_update_required'=>0,'is_lock'=>0,'is_approval_required'=>0,'bypass_enquiry'=>0,'is_closed'=>0,'is_freezable'=>1,'unassign_scam'=>0,'hours_to_freeze'=>null,'freeze_scams_threshold'=>null,'freeze_release_scams_threshold'=>null,'unassign_scam_in_days'=>30,'created_at'=>$now,'updated_at'=>$now ],

            [ 'id'=>55,'index'=>55,'slug'=>'Interested','title'=>'Interested','type'=>'sales','notify_after_days'=>null,'remainder_after_hours'=>0,'customer_enquiry_notify_role_id'=>null,'cap_scams'=>null,'cap_last_days'=>null,'is_file_required'=>0,'is_data_update_required'=>0,'is_scam_type_update_required'=>0,'is_lock'=>0,'is_approval_required'=>0,'bypass_enquiry'=>0,'is_closed'=>0,'is_freezable'=>1,'unassign_scam'=>0,'hours_to_freeze'=>null,'freeze_scams_threshold'=>null,'freeze_release_scams_threshold'=>null,'unassign_scam_in_days'=>15,'created_at'=>$now,'updated_at'=>$now ],

            /* ================= DRAFTING ================= */

            [ 'id'=>14,'index'=>1,'slug'=>'first_call_done','title'=>'First Call Done','type'=>'drafting','notify_after_days'=>null,'remainder_after_hours'=>0,'customer_enquiry_notify_role_id'=>null,'cap_scams'=>null,'cap_last_days'=>null,'is_file_required'=>1,'is_data_update_required'=>0,'is_scam_type_update_required'=>0,'is_lock'=>0,'is_approval_required'=>0,'bypass_enquiry'=>0,'is_closed'=>0,'is_freezable'=>0,'unassign_scam'=>0,'hours_to_freeze'=>null,'freeze_scams_threshold'=>null,'freeze_release_scams_threshold'=>null,'unassign_scam_in_days'=>null,'created_at'=>$now,'updated_at'=>$now ],

            [ 'id'=>15,'index'=>2,'slug'=>'case_discussed','title'=>'Case Discussed','type'=>'drafting','notify_after_days'=>null,'remainder_after_hours'=>0,'customer_enquiry_notify_role_id'=>null,'cap_scams'=>null,'cap_last_days'=>null,'is_file_required'=>0,'is_data_update_required'=>1,'is_scam_type_update_required'=>1,'is_lock'=>0,'is_approval_required'=>0,'bypass_enquiry'=>0,'is_closed'=>0,'is_freezable'=>0,'unassign_scam'=>0,'hours_to_freeze'=>null,'freeze_scams_threshold'=>null,'freeze_release_scams_threshold'=>null,'unassign_scam_in_days'=>null,'created_at'=>$now,'updated_at'=>$now ],

            [ 'id'=>16,'index'=>3,'slug'=>'drafting_in_progress','title'=>'Drafting in Progress','type'=>'drafting','notify_after_days'=>null,'remainder_after_hours'=>0,'customer_enquiry_notify_role_id'=>null,'cap_scams'=>null,'cap_last_days'=>null,'is_file_required'=>0,'is_data_update_required'=>1,'is_scam_type_update_required'=>1,'is_lock'=>0,'is_approval_required'=>0,'bypass_enquiry'=>0,'is_closed'=>0,'is_freezable'=>0,'unassign_scam'=>0,'hours_to_freeze'=>null,'freeze_scams_threshold'=>null,'freeze_release_scams_threshold'=>null,'unassign_scam_in_days'=>null,'created_at'=>$now,'updated_at'=>$now ],

            [ 'id'=>29,'index'=>16,'slug'=>'closed','title'=>'Closed','type'=>'drafting','notify_after_days'=>null,'remainder_after_hours'=>0,'customer_enquiry_notify_role_id'=>6,'cap_scams'=>10,'cap_last_days'=>1,'is_file_required'=>1,'is_data_update_required'=>1,'is_scam_type_update_required'=>1,'is_lock'=>1,'is_approval_required'=>1,'bypass_enquiry'=>1,'is_closed'=>1,'is_freezable'=>0,'unassign_scam'=>0,'hours_to_freeze'=>null,'freeze_scams_threshold'=>null,'freeze_release_scams_threshold'=>null,'unassign_scam_in_days'=>null,'created_at'=>$now,'updated_at'=>$now ],

        ];

        foreach ($statuses as $status) {
            DB::table('scam_statuses')->updateOrInsert(
                ['id' => $status['id']],
                $status
            );
        }
    }
}
