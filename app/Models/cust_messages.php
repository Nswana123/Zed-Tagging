<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cust_messages extends Model
{
    use HasFactory;
    protected $table = 'cust_messages';
    protected $fillable = [
       
        'msisdn',
        'message_id', 
    ];
    public function messages()
    {
        return $this->belongsTo(Messages::class, 'message_id');
    }
}
