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
        Schema::create('service_order_mechanics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained()->restrictOnDelete();
            $table->foreignId('mechanic_id')->constrained('users')->restrictOnDelete();
            $table->string('assignment_role', 30)->nullable();
            $table->timestamp('assigned_at');
            $table->timestamp('unassigned_at')->nullable();
            $table->timestamps();

            $table->index(['service_order_id', 'mechanic_id']);
            $table->index(['mechanic_id', 'unassigned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_order_mechanics');
    }
};
