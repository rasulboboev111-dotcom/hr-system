<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->string('month_year', 7); // e.g., 2026-04
            $table->decimal('salary', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('calculated_total', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['employee_id', 'month_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
    }
};
