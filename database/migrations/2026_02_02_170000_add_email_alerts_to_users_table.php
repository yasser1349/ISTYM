<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'email_alerts')) {
                $table->boolean('email_alerts')->default(true)->after('role');
            }
            if (!Schema::hasColumn('users', 'stock_alerts')) {
                $table->boolean('stock_alerts')->default(true)->after('email_alerts');
            }
            if (!Schema::hasColumn('users', 'order_alerts')) {
                $table->boolean('order_alerts')->default(true)->after('stock_alerts');
            }
            if (!Schema::hasColumn('users', 'maintenance_alerts')) {
                $table->boolean('maintenance_alerts')->default(true)->after('order_alerts');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_alerts', 'stock_alerts', 'order_alerts', 'maintenance_alerts']);
        });
    }
};
