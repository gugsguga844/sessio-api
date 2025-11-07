<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar se a tabela já existe em alguma das versões
        if (!Schema::hasTable('session_participants') && !Schema::hasTable('event_participants')) {
            $tableName = 'session_participants';
            $eventTable = 'sessions';

            Schema::create($tableName, function (Blueprint $table) use ($eventTable) {
                $table->unsignedBigInteger('event_id');
                $table->unsignedBigInteger('client_id');
                $table->primary(['event_id', 'client_id']);

                // Usar o nome correto da tabela de eventos (sessions)
                $table->foreign('event_id')
                    ->references('id')
                    ->on($eventTable)
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
        // Remover ambas as versões da tabela se existirem
        Schema::dropIfExists('session_participants');
        Schema::dropIfExists('event_participants');
    }
};
