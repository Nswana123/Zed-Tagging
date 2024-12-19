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
        Schema::create('fault_type_tbl', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('priority');
            $table->string('fault_type');
            $table->string('ttr_in_hour');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fault_type_tbl');
    }
};
