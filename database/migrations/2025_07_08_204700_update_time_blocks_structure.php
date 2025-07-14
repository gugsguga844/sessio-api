<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_blocks', function (Blueprint $table) {
            // Remover chaves estrangeiras existentes
            // $table->dropForeign(['user_id']); // Removido para evitar erro se não existir
            
            // Adicionar as novas colunas
            if (!Schema::hasColumn('time_blocks', 'color_hex')) {
                $table->string('color_hex', 7)->nullable();
            }
            if (!Schema::hasColumn('time_blocks', 'emoji')) {
                $table->string('emoji', 5)->nullable();
            }
            
            // Remover a coluna color antiga
            // $table->dropColumn('color'); // Removido para evitar erro se não existir
            
            // Alterar o tipo das colunas de data/hora para incluir timezone
            // $table->timestamp('start_time')->change();
            // $table->timestamp('end_time')->change();
            
            // Adicionar índice para melhorar consultas por usuário e período
            $table->index(['user_id', 'start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::table('time_blocks', function (Blueprint $table) {
            // Reverter para a estrutura anterior
            $table->dropForeign(['user_id']);
            
            // Adicionar a coluna color antiga
            $table->string('color')->nullable()->after('emoji');
            
            // Remover as colunas adicionadas
            $table->dropColumn(['color_hex', 'emoji']);
        });
    }
};
