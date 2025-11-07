<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar se a tabela events existe e a sessions não existe
        if (Schema::hasTable('events') && !Schema::hasTable('sessions')) {
            // Renomear a tabela events para sessions
            Schema::rename('events', 'sessions');
        }

        // Verificar se a tabela event_participants existe e session_participants não existe
        if (Schema::hasTable('event_participants') && !Schema::hasTable('session_participants')) {
            // Remover FK antiga se existir (evitar erro ao renomear)
            if (Schema::hasColumn('event_participants', 'event_id')) {
                $fkName = 'event_participants_event_id_foreign';
                $exists = DB::selectOne("SELECT 1 AS x FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'event_participants' AND CONSTRAINT_NAME = ? LIMIT 1", [$fkName]);
                if ($exists) {
                    DB::statement('ALTER TABLE `event_participants` DROP FOREIGN KEY `'.$fkName.'`');
                }
            }

            // Renomear a tabela event_participants para session_participants
            Schema::rename('event_participants', 'session_participants');
        }

        // Se a tabela session_participants existir, atualizar as chaves estrangeiras
        if (Schema::hasTable('session_participants')) {
            // Verificar se a coluna event_id existe
            if (Schema::hasColumn('session_participants', 'event_id')) {
                // Recriar as chaves estrangeiras com os nomes corretos
                if (Schema::hasTable('sessions')) {
                    Schema::table('session_participants', function (Blueprint $table) {
                        $table->foreign('event_id')
                              ->references('id')
                              ->on('sessions')
                              ->onDelete('cascade');
                    });
                }

                // Renomear a coluna event_id para session_id, se ainda não existir
                if (!Schema::hasColumn('session_participants', 'session_id')) {
                    Schema::table('session_participants', function (Blueprint $table) {
                        $table->renameColumn('event_id', 'session_id');
                    });
                }
            }

            // Se a coluna session_id existir, garantir que a chave estrangeira está correta
            if (Schema::hasColumn('session_participants', 'session_id') && Schema::hasTable('sessions')) {
                // Remover FK por nome apenas se existir
                $possibleNames = [
                    'session_participants_session_id_foreign',
                    'session_participants_event_id_foreign',
                ];
                foreach ($possibleNames as $fkName) {
                    $exists = DB::selectOne("SELECT 1 AS x FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'session_participants' AND CONSTRAINT_NAME = ? LIMIT 1", [$fkName]);
                    if ($exists) {
                        DB::statement('ALTER TABLE `session_participants` DROP FOREIGN KEY `'.$fkName.'`');
                    }
                }
                // Recriar a FK correta
                Schema::table('session_participants', function (Blueprint $table) {
                    $table->foreign('session_id')
                          ->references('id')
                          ->on('sessions')
                          ->onDelete('cascade');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Verificar se a tabela session_participants existe e tem a coluna session_id
        if (Schema::hasTable('session_participants') && Schema::hasColumn('session_participants', 'session_id')) {
            // Remover a chave estrangeira atual, se existir
            Schema::table('session_participants', function (Blueprint $table) {
                // Tenta remover a chave estrangeira diretamente
                try {
                    $table->dropForeign(['session_id']);
                } catch (\Exception $e) {
                    // Se falhar, ignora e continua
                }
            });

            // Renomear a coluna de volta para event_id, se não existir
            if (!Schema::hasColumn('session_participants', 'event_id')) {
                Schema::table('session_participants', function (Blueprint $table) {
                    $table->renameColumn('session_id', 'event_id');
                });
            }
        }

        // Renomear as tabelas de volta para os nomes originais, se necessário
        if (Schema::hasTable('sessions')) {
            if (!Schema::hasTable('events')) {
                Schema::rename('sessions', 'events');
            }
        }

        if (Schema::hasTable('session_participants')) {
            if (!Schema::hasTable('event_participants')) {
                Schema::rename('session_participants', 'event_participants');
            }
        }

        // Se a tabela event_participants existir, garantir que a chave estrangeira está correta
        if (Schema::hasTable('event_participants') && Schema::hasTable('events')) {
            // Remover chave estrangeira existente, se houver
            Schema::table('event_participants', function (Blueprint $table) {
                // Tenta remover a chave estrangeira diretamente
                try {
                    $table->dropForeign(['event_id']);
                } catch (\Exception $e) {
                    // Se falhar, ignora e continua
                }
            });

            // Recriar a chave estrangeira
            Schema::table('event_participants', function (Blueprint $table) {
                $table->foreign('event_id')
                      ->references('id')
                      ->on('events')
                      ->onDelete('cascade');
            });
        }
    }
};
