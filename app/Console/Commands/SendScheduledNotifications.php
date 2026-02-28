<?php

namespace App\Console\Commands;

use App\Services\EmailNotificationService;
use Illuminate\Console\Command;

class SendScheduledNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send 
                            {--stock : Send stock alerts only}
                            {--maintenance : Send maintenance reminders only}
                            {--all : Send all notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled email notifications (stock alerts, maintenance reminders)';

    protected EmailNotificationService $emailService;

    public function __construct(EmailNotificationService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sendAll = $this->option('all') || (!$this->option('stock') && !$this->option('maintenance'));

        $this->info('🔔 Envoi des notifications planifiées...');
        $this->newLine();

        // Stock Alerts
        if ($sendAll || $this->option('stock')) {
            $this->info('📦 Vérification des stocks critiques...');
            $stockCount = $this->emailService->checkAllProductsStock();
            $this->info("   ✅ {$stockCount} alerte(s) de stock envoyée(s)");
            $this->newLine();
        }

        // Maintenance Reminders
        if ($sendAll || $this->option('maintenance')) {
            $this->info('🔧 Vérification des maintenances à venir...');
            $maintenanceCount = $this->emailService->checkUpcomingMaintenances();
            $this->info("   ✅ {$maintenanceCount} rappel(s) de maintenance envoyé(s)");
            $this->newLine();
        }

        $this->info('✨ Notifications envoyées avec succès!');

        return Command::SUCCESS;
    }
}
