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
        Schema::create('user_organization_branches', function (Blueprint $table) {
            // $table->ulid('id')->primary()->unique();
            $table->string('user_id', 26);
            $table->string('organization_branch_id', 26);
            $table->string('role_id', 36);
            $table->string('organization_id',26);
            $table->string('description');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_organization_branches');
    }
};
