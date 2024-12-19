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
        Schema::create('noc_assigned_tickets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('engineer_id');
            $table->string('ticket_id');
            $table->string('assigner_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('noc_assigned_tickets');
    }
};
