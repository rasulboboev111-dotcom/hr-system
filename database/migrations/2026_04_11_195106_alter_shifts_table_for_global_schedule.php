<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropUnique(['employee_id', 'date_key']);
        });
        
        DB::statement('ALTER TABLE shifts ALTER COLUMN date_key TYPE varchar(50)');
        DB::statement('ALTER TABLE shifts ALTER COLUMN shift_type TYPE varchar(100)');
    }

    public function down()
    {
        DB::statement('ALTER TABLE shifts ALTER COLUMN date_key TYPE varchar(10)');
        DB::statement('ALTER TABLE shifts ALTER COLUMN shift_type TYPE varchar(10)');

        Schema::table('shifts', function (Blueprint $table) {
            $table->unique(['employee_id', 'date_key']);
        });
    }
};
