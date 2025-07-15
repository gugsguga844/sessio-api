<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->primary('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Terminologia
            $table->string('client_terminology', 20)->default('Cliente'); // "Cliente" ou "Paciente"

            // Preferências da Agenda
            $table->string('default_calendar_view', 20)->default('Weekly');
            $table->integer('visible_calendar_days')->default(5);
            $table->boolean('show_canceled_sessions')->default(false);

            // Outras preferências
            $table->string('interface_theme', 20)->default('Light');
            $table->string('language', 10)->default('pt-BR');

            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
}; 