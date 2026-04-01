<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ FK-safe delete order
        DB::table('model_has_permissions')->delete();
        DB::table('role_has_permissions')->delete();
        DB::table('permissions')->delete();

        $now = Carbon::now();

        DB::table('permissions')->insert([
            ['id'=>1,'name'=>'admin_panel','label'=>'Admin Panel','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,'name'=>'permission:list','label'=>'Permission List','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,'name'=>'permission:update','label'=>'Permission Update','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>4,'name'=>'role:list','label'=>'Role List','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>5,'name'=>'role:create','label'=>'Role Create','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>6,'name'=>'role:update','label'=>'Role Update','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>7,'name'=>'role:delete','label'=>'Role Delete','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>8,'name'=>'user:list','label'=>'User List','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>9,'name'=>'user:create','label'=>'User Create','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>10,'name'=>'user:update','label'=>'User Update','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>11,'name'=>'user:delete','label'=>'User Delete','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>12,'name'=>'customer:list','label'=>'Customer List','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>13,'name'=>'customer:create','label'=>'Customer Create','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>14,'name'=>'customer:update','label'=>'Customer Update','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>15,'name'=>'customer:delete','label'=>'Customer Delete','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>16,'name'=>'scam_lead:list','label'=>'Scam Lead List','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>17,'name'=>'scam_lead:create','label'=>'Scam Lead Create','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>18,'name'=>'scam_lead:update','label'=>'Scam Lead Update','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>19,'name'=>'scam_lead:delete','label'=>'Scam Lead Delete','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>20,'name'=>'scam_lead:transfer','label'=>'Scam Lead Transfer','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>21,'name'=>'scam:list','label'=>'Scam List','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>22,'name'=>'scam:create','label'=>'Scam Create','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>23,'name'=>'scam:update','label'=>'Scam Update','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>24,'name'=>'scam:delete','label'=>'Scam Delete','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>25,'name'=>'scam_type:list','label'=>'Scam Type List','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>26,'name'=>'scam_type:create','label'=>'Scam Type Create','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>27,'name'=>'scam_type:update','label'=>'Scam Type Update','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>28,'name'=>'scam_type:delete','label'=>'Scam Type Delete','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>29,'name'=>'scam_status:list','label'=>'Scam Status List','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>30,'name'=>'scam_status:create','label'=>'Scam Status Create','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>31,'name'=>'scam_status:update','label'=>'Scam Status Update','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>32,'name'=>'scam_status:delete','label'=>'Scam Status Delete','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>33,'name'=>'sales_management','label'=>'Sales Management','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>34,'name'=>'sales_management_self','label'=>'Sales Management Self','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>35,'name'=>'drafting_management','label'=>'Drafting Management','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>36,'name'=>'drafting_management_self','label'=>'Drafting Management Self','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>37,'name'=>'service_management','label'=>'Service Management','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>38,'name'=>'service_management_self','label'=>'Service Management Self','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>39,'name'=>'escalation:list','label'=>'Escalation List','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>40,'name'=>'escalation_self:list','label'=>'Escalation Self List','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>41,'name'=>'escalation:create','label'=>'Escalation Create','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>42,'name'=>'escalation:delete','label'=>'Escalation Delete','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>43,'name'=>'notification:list','label'=>'Notification List','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>44,'name'=>'notification_self:list','label'=>'Notification Self List','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>45,'name'=>'scam_type_filter','label'=>'Scam Type Filter','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>46,'name'=>'sales_assignee_filter','label'=>'Sales Assignee Filter','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>47,'name'=>'sales_status_filter','label'=>'Sales Status Filter','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>48,'name'=>'drafting_assignee_filter','label'=>'Drafting Assignee Filter','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>49,'name'=>'drafting_status_filter','label'=>'Drafting Status Filter','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>50,'name'=>'service_assignee_filter','label'=>'Service Assignee Filter','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>51,'name'=>'scam_created_at_filter','label'=>'Scam Created At Filter','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>52,'name'=>'last_sales_status_updated_at_filter','label'=>'Last Sales Status Updated At Filter','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>53,'name'=>'last_drafting_status_updated_at_filter','label'=>'Last Drafting Status Updated At Filter','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>54,'name'=>'last_sales_assigned_at_filter','label'=>'Last Sales Assigned At Filter','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>55,'name'=>'last_drafting_assigned_at_filter','label'=>'Last Drafting Assigned At Filter','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>56,'name'=>'view_scam_assignee_list','label'=>'View Scam Assignee List','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>57,'name'=>'view_scam_lifecycle','label'=>'View Scam Lifecycle','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>58,'name'=>'view_scam_custom_uploaded_files','label'=>'View Scam Custom Uploaded Files','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>59,'name'=>'view_scam_status_uploaded_files','label'=>'View Scam Status Uploaded Files','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>60,'name'=>'scam_excel_import','label'=>'Scam Excel Import','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>61,'name'=>'login_settings','label'=>'Login Settings','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>62,'name'=>'bypass_disabled_login','label'=>'Bypass Disabled Login','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>63,'name'=>'update_all_users_details','label'=>'Update All Users Details','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>64,'name'=>'change_all_users_password','label'=>'Change All Users Password','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>65,'name'=>'update_locked_sales_status','label'=>'Update Locked Sales Status','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>66,'name'=>'update_locked_drafting_status','label'=>'Update Locked Drafting Status','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>67,'name'=>'view_all_users_activities','label'=>'View All Users Activities','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>68,'name'=>'view_self_users_activities','label'=>'View Self Users Activities','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>69,'name'=>'scam_lead_bulk_delete','label'=>'Scam Lead Bulk Delete','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>70,'name'=>'scam_lead_bulk_transfer','label'=>'Scam Lead Bulk Transfer','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>71,'name'=>'login_as_user','label'=>'Login As User','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>72,'name'=>'show_scam_source','label'=>'Show Scam Source','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>73,'name'=>'scam_source:list','label'=>'Scam Source List','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>74,'name'=>'scam_source:create','label'=>'Scam Source Create','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>75,'name'=>'scam_source:update','label'=>'Scam Source Update','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>76,'name'=>'scam_source:delete','label'=>'Scam Source Delete','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>77,'name'=>'last_service_assigned_at_filter','label'=>'Last Service Assigned At Filter','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>78,'name'=>'customer_enquiry_status:list','label'=>'Customer Enquiry Status List','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>79,'name'=>'customer_enquiry_status:create','label'=>'Customer Enquiry Status Create','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>80,'name'=>'customer_enquiry_status:update','label'=>'Customer Enquiry Status Update','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>81,'name'=>'customer_enquiry_status:delete','label'=>'Customer Enquiry Status Delete','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>82,'name'=>'customer_enquiry:list','label'=>'Customer Enquiry List','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>83,'name'=>'customer_enquiry:delete','label'=>'Customer Enquiry Delete','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>84,'name'=>'customer_enquiry:update_status','label'=>'Customer Enquiry Update Status','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>85,'name'=>'pulse_monitor','label'=>'Pulse Monitor','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>86,'name'=>'phpinfo','label'=>'Phpinfo','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>87,'name'=>'telescope','label'=>'Telescope','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>88,'name'=>'user_preferences','label'=>'User Preferences','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>89,'name'=>'scam_sales_status_review:show','label'=>'Scam Sales Status Review Show','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>90,'name'=>'scam_sales_status_review:update','label'=>'Scam Sales Status Review Update','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>91,'name'=>'scam_drafting_status_review:show','label'=>'Scam Drafting Status Review Show','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>92,'name'=>'scam_drafting_status_review:update','label'=>'Scam Drafting Status Review Update','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>93,'name'=>'dashboard:user_stats','label'=>'Dashboard User Stats','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>94,'name'=>'dashboard:total_scams_chart','label'=>'Dashboard Total Scams Chart','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>95,'name'=>'dashboard:sales_status_stats','label'=>'Dashboard Sales Status Stats','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>96,'name'=>'dashboard:drafting_status_stats','label'=>'Dashboard Drafting Status Stats','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>97,'name'=>'dashboard:customers_by_region_chart','label'=>'Dashboard Customers By Region Chart','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>98,'name'=>'dashboard:scams_by_source_chart','label'=>'Dashboard Scams By Source Chart','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>99,'name'=>'dashboard:recent_scams','label'=>'Dashboard Recent Scams','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
            ['id'=>100,'name'=>'report:user_scam_status','label'=>'Report User Scam Status','guard_name'=>'web','created_at'=>$now,'updated_at'=>$now],
        ]);

        DB::statement('ALTER TABLE permissions AUTO_INCREMENT = 101');
    }
}
