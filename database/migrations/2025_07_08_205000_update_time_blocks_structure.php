<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Apenas exemplo: ajuste conforme a estrutura real desejada para time_blocks
        Schema::table('time_blocks', function (Blueprint $table) {
            if (!Schema::hasColumn('time_blocks', 'color_hex')) {
            $table->string('color_hex', 7)->nullable();
            }
            if (!Schema::hasColumn('time_blocks', 'emoji')) {
            $table->string('emoji', 5)->nullable();
            }
            // Adicionar índice para melhorar consultas por usuário e período
            // $table->index(['user_id', 'start_time', 'end_time']); // Comentado para evitar erro de índice duplicado
        });
    }

    public function down(): void
    {
        Schema::table('time_blocks', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'start_time', 'end_time']);
            $table->dropColumn(['color_hex', 'emoji']);
        });
    }
};
