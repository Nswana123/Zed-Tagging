<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\user_group;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class noc_ticket_tbl extends Model
{
    use HasFactory;
    protected $table = 'noc_ticket_tbl';
    protected $fillable = [
    'case_id',
    'user_id',
    'sla_id',
    'site_name',
    'fault_description',
    'fault_severity',
    'fault_occurrence_time',
    'outage_duration',
    'root_cause',
    'escalation_status', 
    'escalation_group',
    'escalation_date',
    'ticket_status',
    'closed_by',
    'time_taken', 
    'closed_date',
    'sla_compliance'
    ];
    public function attachments()
    {
        return $this->hasMany(noc_attachment::class, 'tickets_id');
    }
    public function faulty_type()
    {
        return $this->belongsTo(fault_type_tbl::class, 'sla_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function noc_assigned_tickets()
    {
        return $this->hasMany(noc_assigned_tickets::class, 'ticket_id');
    }
    public function noc_resolutions()
    {
        return $this->hasMany(noc_resolutions::class, 'ticket_id');
    }
    public function ClosedBy()
{
    return $this->belongsTo(User::class, 'closed_by');
}
public function user_group() {
    return $this->belongsTo(user_group::class, 'escalation_group', 'id');
}

}
