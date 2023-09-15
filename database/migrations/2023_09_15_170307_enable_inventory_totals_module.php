<?php

use App\Modules\InventoryTotals\src\InventoryTotalsServiceProvider;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        InventoryTotalsServiceProvider::enableModule();
    }
};
