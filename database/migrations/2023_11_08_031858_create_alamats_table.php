<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlamatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alamats', function (Blueprint $table) {
            $table->id();
            $table->string('tujuan');
            $table->string('alamat');
            $table->string('ekspedisi')->nullable();
            $table->integer('koli')->default(0);
            $table->string('up')->nullable();
            $table->string('tlp')->nullable();
            $table->string('do');
            $table->string('epur')->nullable();
            $table->string('untuk')->nullable();
            $table->string('nilai')->nullable();
            $table->enum('is_do', ['yes', 'no'])->default('no');
            $table->enum('is_pk', ['yes', 'no'])->default('no');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alamats');
    }
}
