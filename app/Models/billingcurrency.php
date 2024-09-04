<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class billingcurrency extends Model
{
    use HasFactory;
    protected $table = 'billing_currency';

    protected $fillable = [
        'id',
        'currency',
    ];

}
