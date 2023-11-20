<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->string('warehouse_code', 5)->nullable()->after('id');

            $table->index('warehouse_code');

            $table->foreign('warehouse_code')
                ->references('code')
                ->on('warehouses')
                ->cascadeOnDelete();
        });
    }
};
