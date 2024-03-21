<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCreationAudit extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'oms_order_creation_audit';

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
