<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_group extends Model
{
    use HasFactory;
    protected $table = 'user_group';
    protected $fillable = [
        'id',
        'group_name',
        'description',   
    ];
    public function role_permissions()
    {
        return $this->hasMany(role_permissions::class, 'user_group_id');
    }
    public function user_group()
{
    return $this->belongsTo(user_group::class, 'group_id', 'id');
}
public function escalation_group()
{
    return $this->belongsTo(user_group::class, 'escalation_group', 'id');
}
    public function permissions()
    {
        return $this->belongsToMany(permissions::class, 'role_permissions', 'user_group_id', 'permission_id');
    }
    public function user()
    {
        return $this->hasMany(User::class, 'group_id', 'id'); // Assuming group_id is the foreign key in users table
    }
    
}
