<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class role_permissions extends Model
{
    use HasFactory;
    protected $table = 'role_permissions';
    protected $fillable = [
        'user_group_id',
        'permission_id',   
    ];
    public function user_group()
    {
        return $this->belongsTo(user_group::class, 'user_group_id');
    }

    public function permissions()
    {
        return $this->belongsTo(permissions::class, 'permission_id');
    }
}
