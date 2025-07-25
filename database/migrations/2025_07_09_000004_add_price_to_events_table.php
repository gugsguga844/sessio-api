<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar se a tabela sessions existe
        if (Schema::hasTable('sessions')) {
            $tableName = 'sessions';
        } 
        // Se não existir, verificar se a tabela events existe
        elseif (Schema::hasTable('events')) {
            $tableName = 'events';
        } 
        // Se nenhuma das tabelas existir, não há nada para fazer
        else {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            $table->decimal('price', 8, 2)->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        // Verificar se a tabela sessions existe
        if (Schema::hasTable('sessions')) {
            $tableName = 'sessions';
        } 
        // Se não existir, verificar se a tabela events existe
        elseif (Schema::hasTable('events')) {
            $tableName = 'events';
        } 
        // Se nenhuma das tabelas existir, não há nada para fazer
        else {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            $table->dropColumn('price');
        });
    }
}; 