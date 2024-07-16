<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;
    protected $table = 'oms_city';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'county_id',
        'city',
    ];

    public function county()
    {
        return $this->belongsTo(County::class, 'county_id', 'id');
    }

}
