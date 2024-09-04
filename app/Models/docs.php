<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class docs extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'stl_docs';

    protected $fillable = [
        'id',
        'client_id',
        'user_id',
        'doc_type_id',
        'document_name',
        'doc',
        'doc_type',
        'signed_date',
        'effective_date',
        'expiry_date',
        'Notes',
        'datetime',

    ];


}
