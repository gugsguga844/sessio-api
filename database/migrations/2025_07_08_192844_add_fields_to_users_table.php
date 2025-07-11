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
        // Primeiro, verificar se a tabela users existe
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Adicionar a coluna phone se não existir
                if (!Schema::hasColumn('users', 'phone')) {
                    $table->string('phone')->nullable()->after('email');
                }

                // Verificar se a coluna name existe antes de renomear
                if (Schema::hasColumn('users', 'name') && !Schema::hasColumn('users', 'username')) {
                    $table->renameColumn('name', 'username');
                }

                // Adicionar novos campos se não existirem
                if (!Schema::hasColumn('users', 'full_name')) {
                    $table->string('full_name')->after('id');
                }
                
                if (!Schema::hasColumn('users', 'avatar_url')) {
                    $table->string('avatar_url')->nullable()->after('phone');
                }
                
                if (!Schema::hasColumn('users', 'specialty')) {
                    $table->string('specialty', 100)->nullable()->after('avatar_url');
                }
                
                if (!Schema::hasColumn('users', 'professional_license')) {
                    $table->string('professional_license', 50)->nullable()->after('specialty');
                }
                
                if (!Schema::hasColumn('users', 'cpf_nif')) {
                    $table->string('cpf_nif', 20)->nullable()->after('professional_license');
                }
                
                if (!Schema::hasColumn('users', 'office_address')) {
                    $table->text('office_address')->nullable()->after('cpf_nif');
                }

                // Adicionar soft deletes se não existir
                if (!Schema::hasColumn('users', 'deleted_at')) {
                    $table->softDeletes();
                }

                // Atualizar restrição única de email para considerar soft deletes
                // Usando uma abordagem mais simples para verificar se o índice único existe
                $indexes = collect(\DB::select("SHOW INDEXES FROM users"));
                $hasEmailUnique = $indexes->contains(function ($index) {
                    return $index->Key_name === 'users_email_unique';
                });
                
                if ($hasEmailUnique) {
                    $table->dropUnique(['email']);
                    $table->unique(['email', 'deleted_at']);
                }
            });

            // Atualizar registros existentes
            if (Schema::hasColumn('users', 'full_name') && Schema::hasColumn('users', 'username')) {
                \DB::table('users')
                    ->whereNull('full_name')
                    ->update([
                        'full_name' => \DB::raw('username')
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Reverter renomeação
            $table->renameColumn('username', 'name');

            // Remover colunas adicionadas
            $table->dropColumn([
                'full_name',
                'avatar_url',
                'specialty',
                'professional_license',
                'cpf_nif',
                'office_address',
                'deleted_at'
            ]);

            // Restaurar restrição única de email
            $table->dropUnique(['email', 'deleted_at']);
            $table->unique('email');
        });
    }
};
