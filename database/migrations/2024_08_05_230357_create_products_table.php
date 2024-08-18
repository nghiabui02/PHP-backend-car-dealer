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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->decimal('price', 15, 2);
            $table->date('sale_date')->nullable();
            $table->date('import_date');
            $table->integer('warranty_period');
            $table->integer('seating_capacity');
            $table->decimal('power', 8, 2);
            $table->decimal('torque', 8, 2);
            $table->year('manufacturing_year');
            $table->decimal('top_speed', 8, 2);
            $table->string('color');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
