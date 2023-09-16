<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules_inventory_totals_configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('totals_by_warehouse_tag_max_inventory_id_checked')->default(0);
            $table->timestamps();
        });
    }
};
