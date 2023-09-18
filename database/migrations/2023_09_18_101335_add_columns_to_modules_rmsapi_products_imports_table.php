<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_rmsapi_products_imports', function (Blueprint $table) {
            $table->unsignedBigInteger('inventory_id')->nullable()->after('warehouse_code');
            $table->boolean('is_web_item')->nullable()->after('quantity_on_order');
            $table->string('department_name')->nullable()->after('is_web_item');
            $table->string('category_name')->nullable()->after('department_name');
            $table->string('sub_description_1')->nullable()->after('category_name');
            $table->string('sub_description_2')->nullable()->after('sub_description_1');
            $table->string('sub_description_3')->nullable()->after('sub_description_2');
            $table->string('supplier_name')->nullable()->after('sub_description_3');

            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventory')
                ->cascadeOnDelete();
        });
    }
};
