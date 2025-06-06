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
        Schema::create('temp_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('unique_identifier')->unique();
            $table->integer('current_step')->default(1);
            $table->json('step_data')->nullable();
            $table->boolean('step_1_completed')->default(false);
            $table->boolean('step_2_completed')->default(false);
            $table->boolean('step_3_completed')->default(false);
            $table->boolean('step_4_completed')->default(false);
            $table->boolean('step_5_completed')->default(false);
            $table->timestamps();

            // Index for performance
            $table->index(['unique_identifier', 'current_step']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_registrations');
    }
};
