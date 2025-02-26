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
        Schema::create('system_trail_logins', function (Blueprint $table) {
           // $table->id();
             $table->ulid('id')->primary()->unique();
            $table->string('device_id', 26)->nullable();
            $table->string('user_id', 26);
            $table->string('session_id');
            /*$table->integer('device_id')->unsigned();
            $table->foreign('device_id')->references('id')->on('device_lists');
            $table->integer('user_id')->unsigned();
           $table->foreign('user_id')->references('id')->on('users');*/
            $table->string('broswer_details')->nullable();
            $table->string('ip_address');
            $table->string('branch_id', 26)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */


    public function down(): void
    {
        Schema::dropIfExists('system_trail_logins');
    }
};
