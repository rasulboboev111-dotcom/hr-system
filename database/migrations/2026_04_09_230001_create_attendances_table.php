<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->string('date_key', 10);
            $table->enum('status', ['P', 'L', 'A', 'V']); // Present, Late, Absent, Vacation
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'date_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
