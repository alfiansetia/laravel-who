<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProblemlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('problemlogs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('problem_id');
            $table->date('date');
            $table->string('desc')->nullable();
            $table->timestamps();
            $table->foreign('problem_id')->references('id')->on('problems')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('problemlogs');
    }
}
