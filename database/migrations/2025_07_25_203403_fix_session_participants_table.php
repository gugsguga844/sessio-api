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
        // Se a tabela event_participants existir e session_participants não existir, renomear
        if (Schema::hasTable('event_participants') && !Schema::hasTable('session_participants')) {
            // Renomear a tabela
            Schema::rename('event_participants', 'session_participants');
            
            // Remover chaves estrangeiras antigas se existirem
            Schema::table('session_participants', function (Blueprint $table) {
                // Tentar remover a chave estrangeira antiga (pode ter nomes diferentes)
                try {
                    $table->dropForeign(['event_id']);
                } catch (\Exception $e) {
                    // Ignorar se não existir
                }
                
                // Adicionar a nova chave estrangeira para sessions
                $table->foreign('event_id')
                    ->references('id')
                    ->on('sessions')
                    ->onDelete('cascade');
            });
            
            // Renomear a coluna event_id para session_id se necessário
            if (Schema::hasColumn('session_participants', 'event_id') && 
                !Schema::hasColumn('session_participants', 'session_id')) {
                Schema::table('session_participants', function (Blueprint $table) {
                    $table->renameColumn('event_id', 'session_id');
                });
                
                // Atualizar a chave primária
                Schema::table('session_participants', function (Blueprint $table) {
                    $table->dropPrimary(['event_id', 'client_id']);
                    $table->primary(['session_id', 'client_id']);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter as alterações se necessário
        if (Schema::hasTable('session_participants') && !Schema::hasTable('event_participants')) {
            // Renomear de volta para event_participants
            Schema::rename('session_participants', 'event_participants');
            
            // Reverter as colunas se necessário
            if (Schema::hasColumn('event_participants', 'session_id')) {
                Schema::table('event_participants', function (Blueprint $table) {
                    $table->renameColumn('session_id', 'event_id');
                });
                
                // Atualizar a chave primária
                Schema::table('event_participants', function (Blueprint $table) {
                    $table->dropPrimary(['session_id', 'client_id']);
                    $table->primary(['event_id', 'client_id']);
                });
            }
            
            // Atualizar as chaves estrangeiras
            Schema::table('event_participants', function (Blueprint $table) {
                // Remover chave estrangeira existente
                try {
                    $table->dropForeign(['session_id']);
                } catch (\Exception $e) {
                    // Ignorar se não existir
                }
                
                // Adicionar a chave estrangeira para events
                $table->foreign('event_id')
                    ->references('id')
                    ->on('events')
                    ->onDelete('cascade');
            });
        }
    }
};
