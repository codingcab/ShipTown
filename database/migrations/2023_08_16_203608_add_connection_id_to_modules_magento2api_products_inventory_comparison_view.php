<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            CREATE OR REPLACE VIEW modules_magento2api_products_prices_comparison_view AS
            SELECT
                modules_magento2api_products.connection_id as modules_magento2api_connection_id,
                modules_magento2api_products.id as modules_magento2api_products_id,
                products.sku,
                modules_magento2api_connections.magento_store_id,
                modules_magento2api_products.magento_price,
                products_prices.price as expected_price,

                modules_magento2api_products.magento_sale_price,
                products_prices.sale_price as expected_sale_price,

                modules_magento2api_products.magento_sale_price_start_date,
                products_prices.sale_price_start_date as expected_sale_price_start_date,

                modules_magento2api_products.magento_sale_price_end_date,
                products_prices.sale_price_end_date as expected_sale_price_end_date,

                modules_magento2api_products.base_prices_fetched_at,
                modules_magento2api_products.special_prices_fetched_at


            FROM modules_magento2api_products
            LEFT JOIN products ON products.id = modules_magento2api_products.product_id

            LEFT JOIN modules_magento2api_connections
              ON modules_magento2api_connections.id = modules_magento2api_products.connection_id

            LEFT JOIN products_prices
              ON products_prices.product_id = modules_magento2api_products.product_id
              AND products_prices.warehouse_id = modules_magento2api_connections.pricing_source_warehouse_id
        ');

        DB::statement("
            CREATE OR REPLACE VIEW modules_magento2api_products_inventory_comparison_view AS
            SELECT
               modules_magento2api_products.connection_id as modules_magento2api_connection_id,
               modules_magento2api_products.id AS modules_magento2api_products_id,
               products.sku AS sku,
               floor(max(modules_magento2api_products.quantity)) AS magento_quantity,
               if((floor(sum(inventory.quantity_available)) < 0), 0, floor(sum(inventory.quantity_available))) AS expected_quantity,
               modules_magento2api_products.stock_items_fetched_at

            from modules_magento2api_products

            left join modules_magento2api_connections
              ON modules_magento2api_connections.id = modules_magento2api_products.connection_id

            left join taggables
              ON taggables.tag_id = modules_magento2api_connections.inventory_source_warehouse_tag_id
              AND taggables.taggable_type = 'App\\\\Models\\\\Warehouse'

            left join warehouses
              ON warehouses.id = taggables.taggable_id

            left join inventory
              on inventory.product_id = modules_magento2api_products.product_id
              and inventory.warehouse_id = warehouses.id

            left join products
              on products.id = modules_magento2api_products.product_id

            group by modules_magento2api_products.id
        ");
    }
};
