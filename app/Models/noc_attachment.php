<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class noc_attachment extends Model
{
    protected $table = 'noc_attachment';
    protected $fillable = ['file_path', 'tickets_id'];
    public function ticket()
    {
        return $this->belongsTo(noc_icket_tbl::class, 'tickets_id');
    }
}  

