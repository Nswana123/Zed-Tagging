<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class messages extends Model
{
    use HasFactory;
    protected $table = 'messages';
    protected $fillable = [
       
        'name',
        'message', 
    ];
    public function cust_messages()
    {
        return $this->hasMany(cust_messages::class, 'message_id');
    }
}
