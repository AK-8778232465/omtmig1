<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OmsAttachmentHistory extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'oms_attachment_history';

    // Define the primary key
    protected $primaryKey = 'id';

    // Disable timestamps if they are not used
    public $timestamps = false;

    // Specify which attributes are mass assignable
    protected $fillable = [
        'order_id',
        'file_name',
        'updated_by',
        'file_path',
        'action',
        'is_delete',
        'updated_at'
    ];

    // Define any relationships (example: belongsTo for `updated_by` as a user)
    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

