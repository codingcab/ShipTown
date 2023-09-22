<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules_magento2api_configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('last_product_id_checked')->default(0);
            $table->timestamps();
        });
    }
};
