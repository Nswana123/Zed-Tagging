<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class noc_assigned_tickets extends Model
{
    protected $table = 'noc_assigned_tickets';
    protected $fillable = [
    'engineer_id',
    'ticket_id',
    'assigner_id'
    ];
    public function engineer()
    {
        return $this->belongsTo(User::class, 'engineer_id');
    }
    
    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigner_id');
    }
    // Link back to noc_ticket_tbl
    public function noc_ticket_tbl()
    {
        return $this->belongsTo(noc_ticket_tbl::class, 'ticket_id');
    }
}
