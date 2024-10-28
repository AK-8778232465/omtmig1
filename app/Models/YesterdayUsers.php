<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YesterdayUsers extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'yesterday_active_users';

    protected $fillable = [
       'id', 'user_id', 'logged_in', 'created_at'
    ];
}
