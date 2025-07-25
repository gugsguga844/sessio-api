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
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");
        
        // Apenas para MySQL/MariaDB
        if ($driver === 'mysql') {
            $tableName = Schema::hasTable('sessions') ? 'sessions' : 'events';
            
            // Verificar e remover índices duplicados
            $indexes = \DB::select("SHOW INDEX FROM `{$tableName}`");
            $indexGroups = [];
            
            // Agrupar índices por coluna
            foreach ($indexes as $index) {
                $indexName = $index->Key_name;
                $columnName = $index->Column_name;
                
                // Ignorar índices primários
                if ($indexName === 'PRIMARY') continue;
                
                // Adicionar ao grupo de índices para esta coluna
                if (!isset($indexGroups[$columnName])) {
                    $indexGroups[$columnName] = [];
                }
                $indexGroups[$columnName][] = $indexName;
            }
            
            // Para cada coluna com múltiplos índices, manter apenas o primeiro
            foreach ($indexGroups as $column => $indexNames) {
                if (count($indexNames) > 1) {
                    // Manter o primeiro índice, remover os demais
                    for ($i = 1; $i < count($indexNames); $i++) {
                        $indexName = $indexNames[$i];
                        // Verificar se o índice existe antes de tentar removê-lo
                        if (Schema::hasIndex($tableName, $indexName)) {
                            Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                                $table->dropIndex($indexName);
                            });
                        }
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não há como reverter com segurança, pois não sabemos quais índices foram removidos
        // Esta migração é considerada irreversível
    }
};
