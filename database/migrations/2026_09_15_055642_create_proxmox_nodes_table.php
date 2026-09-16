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
        Schema::create('proxmox_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proxmox_server_id')->constrained()->cascadeOnDelete();
            $table->string('node_name');
            $table->unsignedInteger('cpu_total')->default(0);
            $table->decimal('cpu_used', 5, 2)->default(0);
            $table->unsignedBigInteger('ram_total_mb')->default(0);
            $table->unsignedBigInteger('ram_used_mb')->default(0);
            $table->unsignedBigInteger('disk_total_mb')->default(0);
            $table->unsignedBigInteger('disk_used_mb')->default(0);
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['proxmox_server_id', 'node_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proxmox_nodes');
    }
};
