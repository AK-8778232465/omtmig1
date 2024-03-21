<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceUserMapping extends Model
{
    use HasFactory;
    protected $table='oms_user_service_mapping';
    public $timestamps = false;

    protected $fillable = ['id','service_id','user_id','is_active'];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function projects()
    {
        return $this->belongsTo(Process::class, 'service_id', 'id');
    }
}
