<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement('ALTER TABLE employees ALTER COLUMN email DROP NOT NULL');
        \DB::statement('ALTER TABLE employees ALTER COLUMN role DROP NOT NULL');
        \DB::statement('ALTER TABLE employees ALTER COLUMN department DROP NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement('ALTER TABLE employees ALTER COLUMN email SET NOT NULL');
        \DB::statement('ALTER TABLE employees ALTER COLUMN role SET NOT NULL');
        \DB::statement('ALTER TABLE employees ALTER COLUMN department SET NOT NULL');
    }
};
