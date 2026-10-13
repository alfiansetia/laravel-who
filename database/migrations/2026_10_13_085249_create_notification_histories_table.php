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
        Schema::create('notification_histories', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32)->default('do_baru')->index();
            $table->string('title');
            $table->text('body');
            $table->string('topic', 128)->default('general');
            $table->string('do_name', 64)->nullable()->index();
            $table->unsignedBigInteger('so_id')->nullable()->index();
            $table->string('origin', 64)->nullable();
            $table->string('partner', 255)->nullable();
            $table->json('payload')->nullable();
            $table->string('channel', 32)->default('fcm_topic');
            $table->string('status', 32)->default('success')->index();
            $table->text('fcm_response')->nullable();
            $table->string('source', 32)->default('scheduler')->index();
            $table->string('triggered_by', 128)->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('notification_histories')->nullOnDelete();
            $table->dateTime('sent_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_histories');
    }
};
