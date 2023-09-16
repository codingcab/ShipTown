<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventory_totals_by_warehouse_tag')) {
            return;
        }

        Schema::create('inventory_totals_by_warehouse_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('tag_id');
            $table->decimal('quantity', 20)->default(0);
            $table->decimal('quantity_reserved', 20)->default(0);
            $table->decimal('quantity_available', 20)->default(0);
            $table->decimal('quantity_incoming', 20)->default(0);
            $table->timestamp('max_inventory_updated_at')->useCurrent();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'tag_id'], 'uk_product_tag');
            $table->index('calculated_at');
            $table->index('product_id');

            $table->foreign('product_id', 'fk_inventory_totals_by_warehouse_tag_product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('tag_id', 'fk_inventory_totals_by_warehouse_tag_tag_id')
                ->references('id')
                ->on('tags')
                ->cascadeOnDelete();
        });
    }
};
