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
        Schema::table('admin_webhook_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_webhook_logs', 'channel_name')) {
                $table->string('channel_name')->nullable();
                $table->index('channel_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_webhook_logs', function (Blueprint $table) {
            $table->dropIndex(['channel_name']);
            $table->dropColumn('channel_name');
        });
    }
};
