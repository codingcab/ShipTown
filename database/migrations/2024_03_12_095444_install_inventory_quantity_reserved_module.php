<?php

use App\Modules\InventoryQuantityReserved\src\InventoryQuantityReservedServiceProvider;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        InventoryQuantityReservedServiceProvider::installModule();
    }
};
