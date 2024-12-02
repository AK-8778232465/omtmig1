<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    // Specify the table name if it doesn't follow Laravel's convention
    protected $table = 'taxes';

    // Specify the primary key if it's not `id`
    protected $primaryKey = 'id';

    // Allow mass assignment for specific fields
    protected $fillable = [
        'order_id',
        'json',
        'updated_by',
        'updated_at'
    ];

    // Disable timestamps if not using Laravel's default `created_at` and `updated_at` fields
    public $timestamps = false;

    // Cast the `json` field to a JSON object
    protected $casts = [
        'json' => 'array',
    ];
}
