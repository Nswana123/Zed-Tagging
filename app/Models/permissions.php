<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class permissions extends Model
{
    use HasFactory;
    protected $table = 'permissions';
    protected $fillable = [
        'name',
        'description',   
    ];
    public function role_permissions()
    {
        return $this->hasMany(role_permissions::class, 'permission_id');
    }
    public function user_group()
    {
        return $this->belongsToMany(user_group::class, 'role_permissions', 'permission_id', 'user_group_id');
    }
}
