<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_magento2api_products', function (Blueprint $table) {
            $table->timestamp('inventory_synced_at')->nullable()->after('magento_sale_price_end_date');
            $table->timestamp('pricing_synced_at')->nullable()->after('inventory_synced_at');

            $table->index('inventory_synced_at');
            $table->index('pricing_synced_at');
        });
    }
};
