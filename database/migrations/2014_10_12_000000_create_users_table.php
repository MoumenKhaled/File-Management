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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('user_name')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->Integer('verification_code')->nullable();
            $table->string('password');
            $table->Integer('number_of_files')->nullable();
            $table->text('role_name')->default('user');
            $table->text('ip_address')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('email');
            $table->index('user_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
