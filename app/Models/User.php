<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
  protected $fillable = [
    'id',
        'fname',
        'lname',
        'email',
        'mobile',
        'status',
        'location',
        'group_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function user_group() {
        
        return $this->belongsTo(user_group::class, 'group_id','id');
    }
    public function tickets()
{
    return $this->hasMany(tickets::class);
    
}
public function claimedTickets()
    {
        return $this->hasMany(user_tickets::class,'ticket_id');
    }
    public function ticket_resolutions()
    {
        return $this->hasMany(ticket_resolutions::class);
    }
    public function users()
    {
        return $this->belongsTo(User::class, 'escalation_group', 'group_id');
    }
    public function escalation_group(): BelongsTo
    {
        return $this->belongsTo(user_group::class, 'group_id');
    }
    public function noc_ticket_tbl()
    {
        return $this->hasMany(noc_ticket_tbl::class);
    }
  
}
