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
        Schema::create('noc_ticket_tbl', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('case_id')->nullable();
            $table->string('user_id');
            $table->string('sla_id');
            $table->string('site_name');
            $table->string('fault_description');
            $table->string('fault_severity');
            $table->string('fault_occurrence_time');
            $table->string('outage_duration');
            $table->string('root_cause')->nullable();
            $table->string('escalation_status')->nullable(); 
            $table->string('escalation_group')->nullable();
            $table->string('escalation_date')->nullable();
            $table->string('ticket_status')->default('open');
            $table->string('closed_by')->nullable();
            $table->string('time_taken')->nullable(); 
            $table->string('closed_date')->nullable();
            $table->string('sla_compliance')->nullable(); 
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('noc_ticket_tbl');
    }
};
