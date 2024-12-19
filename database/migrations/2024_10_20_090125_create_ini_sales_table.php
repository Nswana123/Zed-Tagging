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
        Schema::create('ini_sales', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('msisdn');
            $table->string('primary_no')->nullable();
            $table->string('nrc')->nullable();
            $table->string('title');
            $table->string('fname');
            $table->string('lname');
            $table->string('product_id');
            $table->string('quantity');
            $table->string('amount');
            $table->string('payment_type');
            $table->string('volte_upsell');
            $table->string('zedlife_upsell');
            $table->string('notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ini_sales');
    }
};
