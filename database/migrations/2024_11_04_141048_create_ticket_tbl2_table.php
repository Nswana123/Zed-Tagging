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
        Schema::create('ticket_tbl2', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('case_id')->nullable();
            $table->string('primary_no');
            $table->string('alternative_no')->nullable();
            $table->string('customer_title')->nullable();
            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->string('issue_type');
            $table->string('complaint_category');
            $table->string('issue_detail');
            $table->string('issue_description')->nullable();
            $table->string('interation_status');
            $table->string('route_cause')->nullable();
            $table->string('action_taken')->nullable();
            $table->string('customer_device')->nullable();
            $table->string('physical_address')->nullable();
            $table->string('duration_of_experience')->nullable();
            $table->string('issue_status')->nullable();
            $table->string('service_level')->nullable();
            $table->string('time_taken')->nullable();
            $table->string('comment')->nullable();
            $table->string('ticket_status')->default('open');
            $table->string('closed_date')->nullable();
            $table->string('closed_by')->nullable();
            $table->string('ticked_by')->nullable(); 
            $table->string('sla')->nullable(); 
            $table->string('refund')->nullable(); 
            $table->string('refund_status')->nullable(); 
            $table->string('claim_status')->default('open'); 
            $table->string('claimed_by')->nullable(); 
            $table->string('assigned_by')->nullable(); 
            $table->string('ticket_quality')->default('normal'); 
            $table->string('claim_timestamp');  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_tbl2');
    }
};
