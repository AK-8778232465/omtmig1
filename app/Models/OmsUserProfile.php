<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OmsUserProfile extends Model
{
    use HasFactory;

    // Specify the table associated with the model
    protected $table = 'oms_user_profiles';

    // Specify the primary key (optional if 'id' is the primary key)
    protected $primaryKey = 'id';

    // The attributes that are mass assignable
    protected $fillable = [
        'oms_user_id', 
        'client_id', 
        'lob_id', 
        'process_id', 
        'user_type_id', 
        'reporting_to', 
        'added_by'
    ];

    // Disable automatic timestamps since created_at is set explicitly
    public $timestamps = false;

    // Specify the default value for created_at
    const CREATED_AT = 'created_at';

    // Relationships

    // Define the relationship with the OmsUser model (assuming you have a model for it)
    public function omsUser()
    {
        return $this->belongsTo(OmsUser::class, 'oms_user_id');
    }

    // Define the relationship with the Client model (assuming you have a model for it)
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    // Define the relationship with the Lob model (assuming you have a model for it)
    public function lob()
    {
        return $this->belongsTo(Lob::class, 'lob_id');
    }

    // Define the relationship with the Process model (assuming you have a model for it)
    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id');
    }

    // Define the relationship with the UserType model (assuming you have a model for it)
    public function userType()
    {
        return $this->belongsTo(UserType::class, 'user_type_id');
    }

    // Define the relationship with the User model (for reporting_to)
    public function reportingTo()
    {
        return $this->belongsTo(User::class, 'reporting_to');
    }

    // Define the relationship with the User model (for added_by)
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    // Optionally, you can define custom getters or setters as needed
}
