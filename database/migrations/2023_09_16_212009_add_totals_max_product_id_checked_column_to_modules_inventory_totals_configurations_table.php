<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_inventory_totals_configurations', function (Blueprint $table) {
            $table->unsignedBigInteger('totals_max_product_id_checked')->default(0)->after('id');
        });
    }
};
