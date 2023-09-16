<?php

use App\Modules\InventoryTotals\src\Models\InventoryTotalByWarehouseTag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        InventoryTotalByWarehouseTag::query()->truncate();

        Schema::table('inventory_totals_by_warehouse_tag', function (Blueprint $table) {
            $table->timestamp('calculated_at')->nullable()->after('max_inventory_updated_at');

            $table->index('calculated_at');
        });
    }
};
