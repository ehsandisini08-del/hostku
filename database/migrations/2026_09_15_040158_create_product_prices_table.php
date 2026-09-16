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
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('billing_cycle'); // monthly, quarterly, semi_annually, annually, biennially, triennially
            $table->decimal('price', 15, 2);
            $table->decimal('setup_fee', 15, 2)->default(0);
            $table->boolean('is_promo')->default(false);
            $table->decimal('promo_price', 15, 2)->nullable();
            $table->timestamp('promo_start')->nullable();
            $table->timestamp('promo_end')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'billing_cycle']);
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
