<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $table = 'oms_state';

    protected $fillable = [
        'id',
        'state_name',
        'short_code',
        'is_active',
        'eld_date',
        'eld_month',
    ];
}
