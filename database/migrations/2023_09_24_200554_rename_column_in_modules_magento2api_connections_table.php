<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('modules_magento2api_connections', 'inventory_source_warehouse_tag_id')) {
            Schema::table('modules_magento2api_connections', function (Blueprint $table) {
                $table->renameColumn('inventory_source_warehouse_tag_id', 'inventory_totals_tag_id');
            });
        }

        if (Schema::hasColumn('modules_magento2api_products', 'inventory_total_by_warehouse_tag_id')) {
            Schema::table('modules_magento2api_products', function (Blueprint $table) {
                $table->renameColumn('inventory_total_by_warehouse_tag_id', 'inventory_totals_by_warehouse_tag_id');
            });
        }

        if (Schema::hasColumn('modules_magento2api_products', 'magento_price')) {
            Schema::table('modules_magento2api_products', function (Blueprint $table) {
                $table->renameColumn('magento_price', 'price');
            });
        }

        if (Schema::hasColumn('modules_magento2api_products', 'magento_sale_price')) {
            Schema::table('modules_magento2api_products', function (Blueprint $table) {
                $table->renameColumn('magento_sale_price', 'sale_price');
            });
        }

        if (Schema::hasColumn('modules_magento2api_products', 'magento_sale_price_start_date')) {
            Schema::table('modules_magento2api_products', function (Blueprint $table) {
                $table->renameColumn('magento_sale_price_start_date', 'sale_price_start_date');
            });
        }

        if (Schema::hasColumn('modules_magento2api_products', 'magento_sale_price_end_date')) {
            Schema::table('modules_magento2api_products', function (Blueprint $table) {
                $table->renameColumn('magento_sale_price_end_date', 'sale_price_end_date');
            });
        }

        if (Schema::hasColumn('modules_magento2api_products', 'magento_sale_price')) {
            Schema::table('modules_magento2api_products', function (Blueprint $table) {
                $table->renameColumn('magento_sale_price', 'sale_price');
            });
        }
    }
};
