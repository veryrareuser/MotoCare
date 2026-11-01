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
        Schema::create('motorcycle_compatibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sparepart_id')->constrained()->restrictOnDelete();
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->unsignedSmallInteger('year_from')->nullable();
            $table->unsignedSmallInteger('year_to')->nullable();
            $table->timestamps();

            $table->index(['brand', 'model']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motorcycle_compatibilities');
    }
};
