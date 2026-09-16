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
        Schema::create('domain_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('tld', 20);
            $table->decimal('registration_price', 15, 2);
            $table->decimal('renewal_price', 15, 2);
            $table->decimal('transfer_price', 15, 2);
            $table->integer('min_years')->default(1);
            $table->integer('max_years')->default(10);
            $table->boolean('is_premium')->default(false);
            $table->timestamps();

            $table->index('tld');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domain_pricings');
    }
};
