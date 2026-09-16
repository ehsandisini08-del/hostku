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
        Schema::create('hosting_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('disk_space_mb');
            $table->unsignedInteger('bandwidth_mb')->nullable();
            $table->unsignedInteger('max_websites')->default(1);
            $table->unsignedInteger('max_databases')->default(1);
            $table->unsignedInteger('max_emails')->default(1);
            $table->unsignedInteger('max_ftp')->default(1);
            $table->unsignedInteger('max_subdomains')->default(0);
            $table->string('server_type', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosting_plans');
    }
};
