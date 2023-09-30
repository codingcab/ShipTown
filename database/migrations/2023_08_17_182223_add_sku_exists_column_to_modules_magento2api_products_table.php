<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('modules_magento2api_products', 'exists_in_magento')) {
            return;
        }

        Schema::table('modules_magento2api_products', function (Blueprint $table) {
            $table->boolean('exists_in_magento')->nullable()->after('product_id');
        });
    }
};
