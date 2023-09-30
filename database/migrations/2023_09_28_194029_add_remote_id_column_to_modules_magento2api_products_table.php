<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_magento2api_products', function (Blueprint $table) {
            $table->string('remote_id', 50)->nullable()->after('exists_in_magento');

            $table->index('remote_id');
        });
    }
};
