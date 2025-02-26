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
        Schema::create('wardrobes', function (Blueprint $table) {
            $table->ulid('id')->primary()->unique();
            $table->string('name', 26);
            $table->string('category_id', 26);
            $table->string('brand')->nullable();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('material')->nullable();
            $table->string('pattern')->nullable();
            $table->string('purchase_price')->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('purchase_place')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wardrobes');
    }
};
