<?php

namespace App\Models;
use App\Models\attachments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tickets extends Model
{
    use HasFactory;
    protected $table = 'tickets';
    protected $fillable = [
       'case_id',
       'user_id',
       'cat_id',
       'msisdn',
        'primary_no',
        'title',
        'fname',
        'lname',
        'method_of_contact',
        'contact',
        'product_id',
       'issue_description',
       'interaction_status',
       'root_cause',
       'action_taken',
       'device_id',
       'location_id',
       'duration_of_experience',
       'ticket_status',
       'closed_by',
       'time_taken', 
       'refund', 
       'refund_status',
       'issue_status',
       'ticket_quality', 
       'attachments',
       'closed_date',
       'ticket_age', 
       'escalation_status', 
       'escalation_group', 
    ];
    public function ticket_category() {
        return $this->belongsTo(ticket_category::class, 'cat_id', 'id');
    }
    public function user_group() {
        return $this->belongsTo(user_group::class, 'escalation_group', 'id');
    }
    public function attachments()
    {
        return $this->hasMany(attachment::class, 'tickets_id');
    }
    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function claimer()
    {
        return $this->hasOneThrough(User::class, user_tickets::class, 'ticket_id', 'id', 'id', 'user_id');
    }
    public function user_tickets()
    {
        return $this->hasMany(user_tickets::class,'ticket_id');
    }
    public function ticket_resolutions()
{
    return $this->hasMany(ticket_resolutions::class, 'ticket_id');
}

public function ClosedBy()
{
    return $this->belongsTo(User::class, 'closed_by');
}
public function escalation_user()
{
    return $this->belongsTo(User::class, 'escalation_group', 'id');
}
public function device()
{
    return $this->belongsTo(devices::class, 'device_id');
}

// Define the relationship with CustomerLocation (location_id foreign key)
public function customerLocation()
{
    return $this->belongsTo(customer_locations::class, 'location_id');
}
public function products()
{
    return $this->belongsTo(products::class, 'product_id');
}
protected $casts = [
    'action_taken' => 'array',  // This ensures action_taken is treated as an array
];


}
