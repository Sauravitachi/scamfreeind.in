<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Constants\Role;
use Illuminate\Console\Command;

class UpdateUserStatusByTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-status {status : The status to set (active or inactive)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically activate or deactivate users based on time, excluding Super Admins';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $statusInput = $this->argument('status');
        
        if (!in_array($statusInput, ['active', 'inactive'])) {
            $this->error('Invalid status. Use "active" or "inactive".');
            return Command::FAILURE;
        }

        $isActive = $statusInput === 'active';
        $statusValue = $isActive ? 1 : 0;

        $superAdminRole = Role::SUPER_ADMIN->value;

        // Find users who are NOT Super Admins
        $usersToUpdateQuery = User::whereDoesntHave('roles', function ($q) use ($superAdminRole) {
            $q->where('name', $superAdminRole);
        });

        $count = $usersToUpdateQuery->update(['status' => $statusValue]);

        $this->info("Successfully updated {$count} users to {$statusInput}.");

        return Command::SUCCESS;
    }
}
