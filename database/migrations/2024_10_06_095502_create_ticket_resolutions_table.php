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
        Schema::create('ticket_resolutions', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('ticket_id');
            $table->string('resolution_remarks')->nullable();
            $table->string('opened')->default('opened');
            $table->string('closed')->default('closed');
            $table->timestamp('resolution_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_resolutions');
    }
};
