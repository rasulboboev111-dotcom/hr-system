<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->string('date_key', 10); // e.g. 2026-04-09
            $table->string('shift_type', 10); // 'day' | 'night'
            $table->timestamps();
            
            $table->unique(['employee_id', 'date_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
