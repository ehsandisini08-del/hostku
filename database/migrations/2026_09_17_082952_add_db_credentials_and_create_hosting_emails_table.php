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
        Schema::table('hosting_services', function (Blueprint $table) {
            $table->string('db_name', 100)->nullable()->after('server_ip');
            $table->string('db_user', 100)->nullable()->after('db_name');
            $table->text('db_pass')->nullable()->after('db_user');
            $table->string('php_version', 20)->nullable()->default('8.4')->after('db_pass');
            $table->boolean('ssl_active')->default(false)->after('php_version');
        });

        Schema::create('hosting_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hosting_service_id')->constrained()->cascadeOnDelete();
            $table->string('email_address')->unique();
            $table->string('mailbox_user', 100);
            $table->string('domain', 255);
            $table->unsignedInteger('quota_mb')->default(500);
            $table->timestamps();
            $table->index(['hosting_service_id', 'email_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosting_emails');

        Schema::table('hosting_services', function (Blueprint $table) {
            $table->dropColumn(['db_name', 'db_user', 'db_pass', 'php_version', 'ssl_active']);
        });
    }
};
