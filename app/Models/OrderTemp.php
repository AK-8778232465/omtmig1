<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTemp extends Model
{

    use HasFactory;
    protected $fillable = [
        'order_id',
        'order_date',
        'process_code',
        'property_state',
        'property_county',
        'order_status',
        'assignee',
        'comments',
        'created_by'
    ];

    protected $table = 'oms_order_temp_table';
    public $timestamps = false;
}
