<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class devices extends Model
{
    use HasFactory;
    protected $table = 'devices';
    protected $fillable = [
       
        'brand',
        'model',
    ];
}
