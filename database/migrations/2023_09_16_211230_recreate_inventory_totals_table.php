<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('inventory_totals');

        Schema::create('inventory_totals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 20)->default(0);
            $table->decimal('quantity_reserved', 20)->default(0);
            $table->decimal('quantity_available', 20)->default(0);
            $table->decimal('quantity_incoming', 20)->default(0);
            $table->timestamp('max_inventory_updated_at')->default('2000-01-01 00:00:00');
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->index('product_id');
            $table->index('calculated_at');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
        });
    }
};
