<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->index('department');
            $table->index('status');
            $table->index('email');
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->index('department');
            $table->index('status');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['employee_id', 'date_key']);
        });

        Schema::table('payroll_records', function (Blueprint $table) {
            $table->index(['employee_id', 'month_year']);
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['department']);
            $table->dropIndex(['status']);
            $table->dropIndex(['email']);
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->dropIndex(['department']);
            $table->dropIndex(['status']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['employee_id', 'date_key']);
        });

        Schema::table('payroll_records', function (Blueprint $table) {
            $table->dropIndex(['employee_id', 'month_year']);
        });
    }
};
