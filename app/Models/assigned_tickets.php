<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class assigned_tickets extends Model
{
    use HasFactory;
    protected $table = 'assigned_tickets';
    protected $fillable = [
    'user_id',
    'assigner_id',
    'ticket_id',
    'assignment_date',
    ];
}
