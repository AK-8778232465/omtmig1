<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountyInstructions extends Model
{
    use HasFactory;
    protected $table = 'county_instructions';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'state_id',
        'county_id',
        'city_id',
        'client_id',
        'process_id',
        'lob_id',
        'json',
        'last_updated_by',
        'created_at',
        'updated_at'
    ];
}
