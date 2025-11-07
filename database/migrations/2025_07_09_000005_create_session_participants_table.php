<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar se a tabela já existe para evitar erro de duplicidade
        if (!Schema::hasTable('session_participants')) {
            // Verificar se a tabela sessions existe
            $sessionTable = Schema::hasTable('sessions') ? 'sessions' : 'events';

            Schema::create('session_participants', function (Blueprint $table) use ($sessionTable) {
                $table->unsignedBigInteger('session_id');
                $table->unsignedBigInteger('client_id');
                $table->primary(['session_id', 'client_id']);
                $table->foreign('session_id')
                      ->references('id')
                      ->on($sessionTable)
                      ->onDelete('cascade');
                $table->foreign('client_id')
                      ->references('id')
                      ->on('clients')
                      ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('session_participants');
    }
};
