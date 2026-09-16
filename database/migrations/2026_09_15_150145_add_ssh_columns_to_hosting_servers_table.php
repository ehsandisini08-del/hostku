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
        Schema::table('hosting_servers', function (Blueprint $table) {
            $table->unsignedInteger('ssh_port')->nullable()->default(22);
            $table->string('ssh_user', 100)->nullable();
            $table->string('ssh_key_path')->nullable();
            $table->string('web_server', 50)->nullable()->default('nginx');
            $table->string('php_version', 20)->nullable()->default('8.3');
            $table->string('base_path')->nullable()->default('/var/www');
            $table->string('ssl_email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hosting_servers', function (Blueprint $table) {
            $table->dropColumn(['ssh_port', 'ssh_user', 'ssh_key_path', 'web_server', 'php_version', 'base_path', 'ssl_email']);
        });
    }
};
