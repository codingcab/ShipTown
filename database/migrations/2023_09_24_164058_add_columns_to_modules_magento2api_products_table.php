<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_magento2api_products', function (Blueprint $table) {
            $table->unsignedBigInteger('inventory_total_by_warehouse_tag_id')->nullable()->after('product_id');
            $table->unsignedBigInteger('product_price_id')->nullable()->after('inventory_total_by_warehouse_tag_id');

            $table->foreign('inventory_total_by_warehouse_tag_id', 'inventory_total_by_warehouse_tag_id_fk')
                ->references('id')
                ->on('inventory_totals_by_warehouse_tag')
                ->onDelete('SET NULL');

            $table->foreign('product_price_id')
                ->references('id')
                ->on('products_prices')
                ->onDelete('SET NULL');
        });
    }
};
