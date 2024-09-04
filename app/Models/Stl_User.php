<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stl_User extends Model
{
    use HasFactory;
    protected $table='stl_user';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'username',
        'password',
        'user_type_id',
        'is_active'
    ];
}
