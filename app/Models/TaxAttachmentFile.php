<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxAttachmentFile extends Model
{
    use HasFactory;

    // Specify the table if it does not follow Laravel's naming convention
    protected $table = 'taxattachement_file';

    // Specify the primary key if it's not 'id'
    protected $primaryKey = 'id';

    // Specify the fields that are mass assignable
    protected $fillable = [
        'order_id',
        'file_path',
        'file_name',
        'created_at'
    ];

    // Disable timestamps if not using 'created_at' and 'updated_at' columns
    public $timestamps = false;
}
