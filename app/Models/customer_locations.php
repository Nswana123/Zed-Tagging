<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class customer_locations extends Model
{
    use HasFactory;
    protected $table = 'customer_locations';
    protected $fillable = [
       
        'province',
        'town',
        'landmark',
    ];
    public function tickets()
{
    return $this->hasMany(tickets::class, 'location_id');
}
}
