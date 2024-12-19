<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class int_user_tickets extends Model
{
    use HasFactory;
    protected $table = 'ini_user_tickets';
    protected $fillable = [
    'user_id',
    'ticket_id',
    'claim_date',
    'assigner_id',
    'assignment_date',
    'assignment_status',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tickets()
    {
        return $this->belongsTo(tickets::class, 'ticket_id');
    }
    public function claimer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigner_id'); 
    }
}
