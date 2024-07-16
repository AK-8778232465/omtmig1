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
        'city_id',
        'agency_code',
        'fips_code',
        'calling',
        'online_script',
        'is_active',
    ];

    public function cities()
    {
        return $this->hasMany(City::class, 'county_id', 'id');
    }
}
