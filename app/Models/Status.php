<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;
    protected $table = 'oms_status';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'status',
    ];
}
