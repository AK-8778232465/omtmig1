<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit_Type extends Model
{
    use HasFactory;
	protected $table='stl_unit_type';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'unit_type',
    ];

}
