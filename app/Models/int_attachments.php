<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class int_attachments extends Model
{
    use HasFactory;
    protected $table = 'ini_attachments';
    protected $fillable = ['file_path', 'tickets_id'];

    public function intTicket()
    {
        return $this->belongsTo(int_ticket::class, 'int_ticket_id');
    }
}
