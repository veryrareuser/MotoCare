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
        Schema::create('maintenance_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motorcycle_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedInteger('due_odometer')->nullable();
            $table->string('status', 20)->default('scheduled');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_date']);
            $table->index(['motorcycle_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_reminders');
    }
};
