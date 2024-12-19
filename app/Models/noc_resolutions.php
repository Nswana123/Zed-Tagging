<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class noc_resolutions extends Model
{
    protected $table = 'noc_resolutions';
    protected $fillable = [
    'user_id',
    'ticket_id',
    'resolution_remarks',
    'opened',
    'closed',
    ];
    public function noc_ticket_tbl()
    {
        return $this->belongsTo(noc_ticket_tbl::class, 'ticket_id', 'id');
    }

    // Resolution belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function noc_resolutions()
{
    return $this->hasMany(noc_resolutions::class)->orderBy('created_at', 'desc');

}
}
