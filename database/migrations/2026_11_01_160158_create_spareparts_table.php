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
        Schema::create('spareparts', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 80)->unique();
            $table->string('name', 180);
            $table->text('description')->nullable();
            $table->string('unit', 30);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('stock_quantity', 12, 2)->default(0);
            $table->unsignedInteger('minimum_stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spareparts');
    }
};
