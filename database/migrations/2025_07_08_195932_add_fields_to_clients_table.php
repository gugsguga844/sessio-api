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
        Schema::table('clients', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('phone');
            $table->string('cpf_nif', 20)->nullable()->after('birth_date');
            $table->text('emergency_contact')->nullable()->after('cpf_nif');
            $table->text('case_summary')->nullable()->after('emergency_contact');
            $table->string('status', 20)->default('Active')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'birth_date',
                'cpf_nif',
                'emergency_contact',
                'case_summary'
            ]);
            $table->string('status', 20)->default('active')->change();
        });
    }
};
