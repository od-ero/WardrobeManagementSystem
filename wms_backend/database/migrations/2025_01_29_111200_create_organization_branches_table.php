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
        Schema::create('organization_branches', function (Blueprint $table) {
             $table->ulid('id')->primary()->unique();
            $table->string('code')->unique();
            $table->string('key')->nullable();
            $table->string('organization_id', 26);
            $table->string('name');
            $table->string('kra_pin')->unique()->nullable();
            /*$table->integer('device_id')->unsigned();
            $table->foreign('device_id')->references('id')->on('device_lists');
            $table->integer('user_id')->unsigned();
           $table->foreign('user_id')->references('id')->on('users');*/
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique();
            $table->string('phone_2')->unique()->nullable();
            $table->string('logo_url')->nullable();
            $table->string('location')->nullable();
            $table->string('system_login_trail_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_branches');
    }
};
