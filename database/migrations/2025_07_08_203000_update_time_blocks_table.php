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
        Schema::table('time_blocks', function (Blueprint $table) {
            // Adicionar novas colunas
            $table->text('description')->nullable()->after('title');
            $table->boolean('is_recurring')->default(false)->after('end_time');
            $table->string('recurrence_pattern', 20)->nullable()->after('is_recurring');
            
            // Remover coluna color que não será mais utilizada
            $table->dropColumn('color');
            
            // Adicionar índices para melhorar performance nas consultas
            $table->index(['start_time', 'end_time']);
            $table->index('is_recurring');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_blocks', function (Blueprint $table) {
            // Reverter as alterações
            $table->dropIndex(['start_time', 'end_time']);
            $table->dropIndex('is_recurring');
            
            // Recriar coluna color
            $table->string('color')->nullable();
            
            // Remover colunas adicionadas
            $table->dropColumn(['description', 'is_recurring', 'recurrence_pattern']);
        });
    }
};
