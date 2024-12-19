<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ticket_category extends Model
{
    use HasFactory;
    protected $table = 'ticket_category';
    protected $fillable = [
        'category_name',
        'category_detail',
        'category_type',
        'sla_type_id',   
    ];
    public function ticket_sla() {
        return $this->belongsTo(ticket_sla::class, 'sla_type_id');
    }
}
