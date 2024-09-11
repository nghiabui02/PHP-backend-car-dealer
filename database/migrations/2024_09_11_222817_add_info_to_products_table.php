<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('length')->nullable();
            $table->string('wheelbase')->nullable();
            $table->string('ground_clearance')->nullable();
            $table->string('trunk_capacity')->nullable();
            $table->string('fuel_tank_capacity')->nullable();
            $table->string('fuel_consumption_city')->nullable();
            $table->string('fuel_consumption_highway')->nullable();
            $table->string('fuel_consumption_combined')->nullable();
            $table->string('wheel_size', 5)->nullable();
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
