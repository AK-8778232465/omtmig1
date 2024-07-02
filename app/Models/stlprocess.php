<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class stlprocess extends Model
{
    use HasFactory;

    protected $table = 'stl_process';
    public $timestamps = false;

    protected $fillable = ['id', 'name', 'lob_id', 'is_active'];
}
