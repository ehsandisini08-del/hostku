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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain_name')->unique();
            $table->string('tld', 20);
            $table->string('registrar', 100)->nullable();
            $table->string('registrar_id', 100)->nullable();
            $table->string('status', 50)->nullable();
            $table->date('registration_date')->nullable();
            $table->date('expiration_date');
            $table->boolean('transfer_lock')->default(true);
            $table->boolean('auto_renew')->default(true);
            $table->json('nameservers')->nullable();
            $table->boolean('whois_privacy')->default(false);
            $table->timestamps();
            $table->index('domain_name');
            $table->index('expiration_date');
            $table->index('tld');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
