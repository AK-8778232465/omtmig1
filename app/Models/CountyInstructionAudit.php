<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountyInstructionAudit extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'oms_county_instructions_audit';

    protected $fillable = [
        'id',
        'file_name',
        'total_rows',
        'sucessfull_rows',
        'created_at',
        'created_by',
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
