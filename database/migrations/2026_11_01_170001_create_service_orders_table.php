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
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('work_order_number', 40)->unique();
            $table->foreignId('booking_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $table->foreignId('motorcycle_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('odometer_in');
            $table->unsignedInteger('odometer_out')->nullable();
            $table->string('status', 30);
            $table->text('diagnosis')->nullable();
            $table->text('work_notes')->nullable();
            $table->timestamp('checked_in_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['status', 'checked_in_at']);
            $table->index('checked_in_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
