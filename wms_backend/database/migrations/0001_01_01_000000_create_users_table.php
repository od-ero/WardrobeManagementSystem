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
        Schema::create('users', function (Blueprint $table) {
             $table->ulid('id')->primary()->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('id_no')->unique()->nullable();
            $table->string('staff_no')->unique()->nullable();
            $table->string('phone',20)->unique();
            $table->string('second_phone',20)->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('phy_address')->nullable();
            $table->string('description')->nullable();
          /*  $table->string('role_id')->nullable();*/
            $table->string('organization_id', 26)->nullable();
            $table->boolean('special_access');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            //$table->foreignId('user_id')->nullable()->index();
            $table->foreignUlid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
