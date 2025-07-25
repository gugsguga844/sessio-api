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
        if (Schema::hasTable('event_participants')) {
            Schema::drop('event_participants');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recriar a tabela event_participants se necessário
        if (!Schema::hasTable('event_participants') && Schema::hasTable('sessions')) {
            Schema::create('event_participants', function (Blueprint $table) {
                $table->unsignedBigInteger('event_id');
                $table->unsignedBigInteger('client_id');
                $table->primary(['event_id', 'client_id']);
                $table->foreign('event_id')
                      ->references('id')
                      ->on('sessions')
                      ->onDelete('cascade');
                $table->foreign('client_id')
                      ->references('id')
                      ->on('clients')
                      ->onDelete('cascade');
            });
        }
    }
};
