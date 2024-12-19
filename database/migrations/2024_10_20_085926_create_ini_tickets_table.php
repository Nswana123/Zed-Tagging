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
        Schema::create('ini_tickets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('case_id')->nullable();
            $table->string('user_id');
            $table->string('cat_id');
            $table->string('msisdn');
            $table->string('primary_no')->nullable();
            $table->string('title');
            $table->string('fname');
            $table->string('lname');
            $table->string('mthod_of_contact')->nullable();
            $table->string('contact')->nullable();
            $table->string('product_id')->nullable();
            $table->string('issue_description');
            $table->string('interaction_status');
            $table->string('root_cause')->nullable();
            $table->json('action_taken')->nullable(); 
            $table->string('device_id')->nullable();
            $table->string('location_id')->nullable();
            $table->string('duration_of_experience')->nullable();
            $table->string('ticket_status')->default('open');
            $table->string('closed_by')->nullable();
            $table->string('time_taken')->nullable(); 
            $table->string('refund')->nullable(); 
            $table->string('refund_status')->default('open'); 
            $table->string('claim_status')->default('open'); 
            $table->string('issue_status')->nullable(); 
            $table->string('ticket_quality')->default('normal'); 
            $table->string('closed_date')->nullable();
            $table->string('ticket_age')->nullable(); 
            $table->string('escalation_status')->nullable(); 
            $table->string('escalation_group')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ini_tickets');
    }
};
