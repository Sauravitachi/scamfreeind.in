<?php

namespace App\DTO;

class DashboardStats
{
    public function __construct(

        public readonly int $totalUsers,
        public readonly int $totalActiveUsers,
        public readonly int $totalLoggedInUsers,
        public readonly int $totalSalesUsers,
        public readonly int $totalDraftingUsers,
        public readonly int $totalServiceUsers,

        public readonly int $totalScams,
        public readonly int $todaysScams,
        public readonly int $totalSalesAssignedScams,
        public readonly int $todaysSalesAssignedScams,
        public readonly int $totalDraftingAssignedScams,
        public readonly int $todaysDraftingAssignedScams,
    ) {}
}
