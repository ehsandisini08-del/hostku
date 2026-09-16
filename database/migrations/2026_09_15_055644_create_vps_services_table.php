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
        Schema::create('vps_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vps_plan_id')->constrained();
            $table->foreignId('proxmox_node_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('vm_id')->nullable();
            $table->string('hostname')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('ipv6_address', 45)->nullable();
            $table->string('username')->default('root');
            $table->string('os_template', 100)->nullable();
            $table->unsignedInteger('cpu_cores');
            $table->unsignedInteger('ram_mb');
            $table->unsignedInteger('disk_mb');
            $table->unsignedInteger('bandwidth_mb')->nullable();
            $table->string('vm_status', 50)->default('stopped');
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
        Schema::dropIfExists('vps_services');
    }
};
