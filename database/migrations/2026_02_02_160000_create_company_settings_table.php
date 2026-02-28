<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('ISTYM');
            $table->string('ice')->nullable(); // Identifiant Commun de l'Entreprise
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('logo')->nullable();
            $table->string('currency')->default('MAD');
            $table->decimal('tax_rate', 5, 2)->default(20.00);
            $table->timestamps();
        });

        // Insert default settings
        DB::table('company_settings')->insert([
            'name' => 'ISTYM',
            'email' => 'contact@istym.ma',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
