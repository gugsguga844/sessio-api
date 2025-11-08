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
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('client_id')->nullable();

                $table->timestamp('start_time');
                $table->integer('duration_min');

                $table->text('focus_topic')->nullable();
                $table->text('session_notes')->nullable();

                $table->string('type', 20); // In-person | Online
                $table->string('meeting_url')->nullable();

                $table->string('payment_status', 20)->default('Pending'); // Pending | Paid
                $table->string('payment_method', 50)->nullable();
                $table->decimal('price', 10, 2)->nullable();

                $table->string('session_status', 20)->default('Scheduled'); // Scheduled | Completed | Canceled

                $table->timestamps();

                // Indexes
                $table->index('start_time');
                $table->index('session_status');

                // FKs (sem cascade para user/client; cascade lógico acontece em app)
                $table->foreign('user_id')->references('id')->on('users');
                $table->foreign('client_id')->references('id')->on('clients');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
