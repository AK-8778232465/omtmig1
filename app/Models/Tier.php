<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tier extends Model
{
    use HasFactory;
	protected $table = 'oms_tier';
    public $timestamps = false;
    protected $fillable = [
        'id',
        'tier_id',
    ];
}
