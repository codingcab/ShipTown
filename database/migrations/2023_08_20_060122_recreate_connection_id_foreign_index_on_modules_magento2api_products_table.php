<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_magento2api_products', function (Blueprint $table) {
            $table->dropForeign(['connection_id']);
        });

        Schema::table('modules_magento2api_products', function (Blueprint $table) {
            $table->foreign('connection_id')
                ->references('id')
                ->on('modules_magento2api_connections')
                ->cascadeOnDelete();
        });
    }
};
