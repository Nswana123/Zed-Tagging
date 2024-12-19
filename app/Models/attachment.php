<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class attachment extends Model
{
    use HasFactory;
    protected $table = 'attachments';
    protected $fillable = ['file_path', 'tickets_id'];

    public function tickets()
    {
        return $this->belongsTo(tickets::class);
    }
}
