<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    // id, inventory_id, product_sku, warehouse_code, quantity_reserved, reason, custom_uuid
    public function up(): void
    {
        Schema::create('inventory_reservations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('inventory_id')->unsigned();
            $table->string('product_sku', 50);
            $table->string('warehouse_code', 5);
            $table->decimal('quantity_reserved', 20, 2);
            $table->string('comment')->default('');
            $table->string('custom_uuid')->unique()->nullable();
            $table->timestamps();

            $table->foreign('warehouse_code')
                ->references('code')
                ->on('warehouses')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('product_sku')
                ->references('sku')
                ->on('products')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventory')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }
};
