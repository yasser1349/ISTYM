<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\NotificationService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule stock and maintenance alerts check every 5 minutes
Schedule::call(function () {
    $service = app(NotificationService::class);
    $service->checkStockAlerts();
    $service->checkMaintenanceAlerts();
})->everyFiveMinutes()->name('check-alerts');

// Update dashboard stats every minute
Schedule::call(function () {
    $service = app(NotificationService::class);
    $service->clearStatsCache();
})->everyMinute()->name('clear-stats-cache');
