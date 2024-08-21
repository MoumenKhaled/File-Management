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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_file_id');
            $table->string('user_name');
            $table->string('owner_file_name');
            $table->unsignedBigInteger('group_id');
            $table->string('group_name');
            $table->string('file_name');
            $table->unsignedBigInteger('file_id');
            $table->timestamp('check_in_date');
            $table->timestamp('check_out_date')->nullable();
            $table->timestamps();

            $table->index('file_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
};
