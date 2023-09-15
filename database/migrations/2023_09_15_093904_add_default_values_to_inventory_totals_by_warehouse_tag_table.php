<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('inventory_totals_by_warehouse_tag');

        Schema::create('inventory_totals_by_warehouse_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('tag_id');
            $table->unsignedBigInteger('quantity')->default(0);
            $table->unsignedBigInteger('quantity_reserved')->default(0);
            $table->unsignedBigInteger('quantity_available')->default(0);
            $table->unsignedBigInteger('quantity_incoming')->default(0);
            $table->timestamp('max_inventory_updated_at')->useCurrent();
            $table->timestamps();

            $table->unique(['product_id', 'tag_id'], 'uk_product_tag');

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
