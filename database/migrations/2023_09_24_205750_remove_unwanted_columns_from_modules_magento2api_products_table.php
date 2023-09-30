<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_magento2api_products', function (Blueprint $table) {
            $table->dropColumn('is_in_stock');
            $table->dropColumn('is_inventory_in_sync');
        });
    }
};
