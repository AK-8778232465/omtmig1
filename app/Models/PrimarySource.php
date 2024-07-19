<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrimarySource extends Model
{
    use HasFactory;
	protected $table='oms_primary_source';
    public $timestamps = false;
    protected $fillable = [
        'id',
        'source_name'
    ];

}
