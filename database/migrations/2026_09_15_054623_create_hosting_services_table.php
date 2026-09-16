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
        Schema::create('hosting_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hosting_plan_id')->constrained();
            $table->foreignId('hosting_server_id')->constrained();
            $table->string('domain')->nullable();
            $table->string('username', 100)->nullable();
            $table->string('server_ip', 45)->nullable();
            $table->string('panel_url')->nullable();
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosting_services');
    }
};
