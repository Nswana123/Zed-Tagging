<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sales extends Model
{
    use HasFactory;
    protected $table = 'sales';
    protected $fillable = [
    'user_id',   
    'msisdn',
    'primary_no',
    'nrc',
    'title',
    'fname',
    'lname',
    'product_id',
    'quantity',
    'amount',
    'payment_type',
    'volte_upsell',
    'zedlife_upsell',
    'notes',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function product()
    {
        return $this->belongsTo(products::class, 'product_id');
    }
}
