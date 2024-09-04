<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service_Audit extends Model
{
    use HasFactory;
    protected $table = 'service_audit';
    public $timestamps = false;
    protected $fillable = [
        'id',
        'description_id',
        'process_name',
        'cost',
        'effective_date',
        'added_by',
        'added_at',
        'unit_type_id',
        'no_of_resources',
        'doc',
        'doc_type',
        'is_active'
    ];
}
