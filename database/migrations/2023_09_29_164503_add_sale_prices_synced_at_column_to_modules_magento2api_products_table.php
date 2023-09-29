<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_magento2api_products', function (Blueprint $table) {
            $table->timestamp('sale_prices_synced_at')->nullable()->after('pricing_synced_at');

            $table->index('sale_prices_synced_at');
        });
    }
};
