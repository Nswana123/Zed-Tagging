<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class int_ticket_resolutions extends Model
{
    use HasFactory;
    protected $table = 'ini_user_ticket_resolutions';
    protected $fillable = [
    'user_id',
    'ticket_id',
    'resolution_remarks',
    'opened',
    'closed',
    'resolution_date',
    ];
    public function tickets()
    {
        return $this->belongsTo(tickets::class, 'ticket_id', 'id');
    }

    // Resolution belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
