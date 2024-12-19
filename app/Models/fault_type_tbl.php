<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class fault_type_tbl extends Model
{
    use HasFactory;
    protected $table = 'fault_type_tbl';
    protected $fillable = [
        'priority',
        'fault_type', 
        'ttr_in_hour',  
    ];

    public function fault_type()
    {
        return $this->hasMany(fault_type::class, 'fault_id');
    }  //
}
