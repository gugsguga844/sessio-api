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
        // Verificar se a tabela events ainda existe
        if (Schema::hasTable('events')) {
            $tableName = 'events';
        } 
        // Se não existir, verificar se a tabela sessions existe
        elseif (Schema::hasTable('sessions')) {
            $tableName = 'sessions';
        } 
        // Se nenhuma das tabelas existir, não há nada para fazer
        else {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            // Remover colunas não utilizadas
            if (Schema::hasColumn($tableName, 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn($tableName, 'end_time')) {
                $table->dropColumn('end_time');
            }
            if (Schema::hasColumn($tableName, 'notes')) {
                $table->dropColumn('notes');
            }

            // Adicionar campos necessários
            if (!Schema::hasColumn($tableName, 'duration_min')) {
            $table->integer('duration_min')->after('start_time');
            }
            if (!Schema::hasColumn($tableName, 'focus_topic')) {
            $table->text('focus_topic')->nullable()->after('duration_min');
            }
            if (!Schema::hasColumn($tableName, 'session_notes')) {
            $table->text('session_notes')->nullable()->after('focus_topic');
            }
            if (!Schema::hasColumn($tableName, 'session_status')) {
                $table->string('session_status', 20)->default('Scheduled')->after('payment_status');
            }
            
            // Garantir tipos e defaults corretos
            $table->timestamp('start_time')->useCurrent()->change();
            $table->string('type', 20)->nullable(false)->change();
            $table->string('payment_status', 20)->default('Pending')->change();
            
            // Adicionar índices apenas se não existirem
            if (!Schema::hasIndex($tableName, $tableName.'_start_time_index')) {
                $table->index('start_time');
            }
            
            if (!Schema::hasIndex($tableName, $tableName.'_session_status_index')) {
                $table->index('session_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Verificar se a tabela events ainda existe
        if (Schema::hasTable('events')) {
            $tableName = 'events';
        } 
        // Se não existir, verificar se a tabela sessions existe
        elseif (Schema::hasTable('sessions')) {
            $tableName = 'sessions';
        } 
        // Se nenhuma das tabelas existir, não há nada para fazer
        else {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            // Remover campos adicionados
            if (Schema::hasColumn($tableName, 'duration_min')) {
                $table->dropColumn('duration_min');
            }
            if (Schema::hasColumn($tableName, 'focus_topic')) {
                $table->dropColumn('focus_topic');
            }
            if (Schema::hasColumn($tableName, 'session_notes')) {
                $table->dropColumn('session_notes');
            }
            if (Schema::hasColumn($tableName, 'session_status')) {
                $table->dropColumn('session_status');
            }
            
            // Recriar colunas removidas
            $table->string('title')->nullable();
            $table->dateTime('end_time');
            $table->text('notes')->nullable();
            
            // Reverter alterações de tipo
            $table->dateTime('start_time')->change();
            $table->string('type')->nullable()->change();
            $table->string('payment_status')->nullable()->change();
            
            // Remover índices
            $table->dropIndex(['start_time']);
            $table->dropIndex(['session_status']);
        });
    }
};
