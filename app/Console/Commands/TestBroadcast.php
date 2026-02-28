<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;
use App\Events\DashboardUpdate;
use App\Models\Product;

class TestBroadcast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'broadcast:test {type=all : Type of notification to broadcast (stock|order|maintenance|stats|all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test broadcasting notifications in real-time';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService)
    {
        $type = $this->argument('type');
        
        $this->info("🚀 Testing broadcast for: {$type}");
        
        switch ($type) {
            case 'stock':
                $this->testStockAlerts($notificationService);
                break;
            case 'maintenance':
                $this->testMaintenanceAlerts($notificationService);
                break;
            case 'stats':
                $this->testDashboardStats($notificationService);
                break;
            case 'all':
            default:
                $this->testStockAlerts($notificationService);
                $this->testMaintenanceAlerts($notificationService);
                $this->testDashboardStats($notificationService);
                break;
        }
        
        $this->info("✅ Broadcast test completed!");
        return Command::SUCCESS;
    }
    
    private function testStockAlerts(NotificationService $service): void
    {
        $this->info("📦 Broadcasting stock alerts...");
        $alerts = $service->checkStockAlerts();
        $this->info("  → Sent " . count($alerts) . " stock alert(s)");
    }
    
    private function testMaintenanceAlerts(NotificationService $service): void
    {
        $this->info("🔧 Broadcasting maintenance alerts...");
        $alerts = $service->checkMaintenanceAlerts();
        $this->info("  → Sent " . count($alerts) . " maintenance alert(s)");
    }
    
    private function testDashboardStats(NotificationService $service): void
    {
        $this->info("📊 Broadcasting dashboard stats...");
        $service->updateDashboardStats();
        $this->info("  → Dashboard stats broadcasted");
    }
}
