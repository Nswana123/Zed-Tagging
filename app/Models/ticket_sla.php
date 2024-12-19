<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ticket_sla extends Model
{
    use HasFactory;
    protected $table = 'ticket_sla';
    protected $fillable = [
        'priority',
        'description', 
        'ttr_in_hour',  
    ];
    public function ticket_sla() {
        return $this->belongsTo(ticket_sla::class, 'sla_type_id');
    }
    public function noc_ticket_tbl()
    {
        return $this->hasMany(noc_ticket_tbl::class, 'sla_id');
    }

}
