<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements_new', function (Blueprint $table) {
            $table->dropForeign(['next_movement_id']);
            $table->dropColumn('next_movement_id');
        });
    }
};
