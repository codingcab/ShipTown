<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('modules_stocktaking_suggestions_configurations', function (Blueprint $table) {
            $table->id();
            $table->date('min_count_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules_stocktaking_suggestions_configurations');
    }
};
