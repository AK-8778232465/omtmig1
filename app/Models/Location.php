<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
	protected $table='stl_process_location';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'location_name',
    ];
}
