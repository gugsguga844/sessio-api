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
        Schema::table('events', function (Blueprint $table) {
            // Remover colunas não utilizadas
            if (Schema::hasColumn('events', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('events', 'end_time')) {
                $table->dropColumn('end_time');
            }
            if (Schema::hasColumn('events', 'notes')) {
                $table->dropColumn('notes');
            }

            // Adicionar campos necessários
            if (!Schema::hasColumn('events', 'duration_min')) {
            $table->integer('duration_min')->after('start_time');
            }
            if (!Schema::hasColumn('events', 'focus_topic')) {
            $table->text('focus_topic')->nullable()->after('duration_min');
            }
            if (!Schema::hasColumn('events', 'session_notes')) {
            $table->text('session_notes')->nullable()->after('focus_topic');
            }
            if (!Schema::hasColumn('events', 'session_status')) {
                $table->string('session_status', 20)->default('Scheduled')->after('payment_status');
            }
            
            // Garantir tipos e defaults corretos
            $table->timestamp('start_time')->useCurrent()->change();
            $table->string('type', 20)->nullable(false)->change();
            $table->string('payment_status', 20)->default('Pending')->change();
            
            // Adicionar índices
            $table->index('start_time');
            $table->index('session_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Remover campos adicionados
            if (Schema::hasColumn('events', 'duration_min')) {
                $table->dropColumn('duration_min');
            }
            if (Schema::hasColumn('events', 'focus_topic')) {
                $table->dropColumn('focus_topic');
            }
            if (Schema::hasColumn('events', 'session_notes')) {
                $table->dropColumn('session_notes');
            }
            if (Schema::hasColumn('events', 'session_status')) {
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
