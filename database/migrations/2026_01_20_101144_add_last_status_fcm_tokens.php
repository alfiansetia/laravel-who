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
        Schema::table('fcm_tokens', function (Blueprint $table) {
            $table->string('last_status')->nullable();
            $table->dateTime('last_status_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fcm_tokens', function (Blueprint $table) {
            $table->dropColumn('last_status');
            $table->dropColumn('last_status_at');
        });
    }
};
