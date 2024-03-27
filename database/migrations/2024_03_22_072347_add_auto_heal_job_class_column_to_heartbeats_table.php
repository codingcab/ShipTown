<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('heartbeats', function (Blueprint $table) {
            $table->string('auto_heal_job_class')->nullable()->after('error_message');
        });
    }
};
