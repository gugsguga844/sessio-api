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
            $table->string('meeting_url', 255)->nullable()->after('type');
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
            $table->dropColumn('meeting_url');
        });
    }
}; 