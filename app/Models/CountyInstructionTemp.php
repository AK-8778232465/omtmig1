<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountyInstructionTemp extends Model
{

    use HasFactory;
    protected $fillable = [
        'client',
        'process',
        'lob',
        'state',
        'county',
        'city',
        'comments',
        'audit_id',
        'created_by'
    ];

    protected $table = 'oms_county_instructions_temp_table';
    public $timestamps = false;
}
