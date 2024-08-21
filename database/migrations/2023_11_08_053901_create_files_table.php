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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_file_id');
            $table->unsignedBigInteger('user_file_id')->nullable();

            $table->unsignedBigInteger('group_id');
            $table->string('name');
            $table->string('file');
            $table->string('status')->default('free');
            $table->unsignedBigInteger('version')->nullable();
            $table->timestamps();

            $table->foreign('group_id')->references('id')->on('groups');
            $table->foreign('owner_file_id')->references('id')->on('users');
            $table->foreign('user_file_id')->references('id')->on('users');

            $table->index(['name', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
};
