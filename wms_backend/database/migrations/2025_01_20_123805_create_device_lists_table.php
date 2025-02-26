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
        Schema::create('device_lists', function (Blueprint $table) {
             $table->ulid('id')->primary()->unique();
            $table->string('organization_id', 26);
            $table->string('branch_id', 26);
            $table->string('device_code');
            $table->string('device_name');
            $table->string('device_mac')->nullable();
            $table->string('description')->nullable();
            $table->string('created_by');
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_lists');
    }
};
