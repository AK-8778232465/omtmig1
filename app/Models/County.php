<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class County extends Model
{
    protected $table = 'oms_county';

    protected $fillable = [
        'stateId',
        'county_name',
        'agency_code',
        'fips_code',
        'calling',
        'online_script',
        'is_active',
    ];

}
