<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class doctypes extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'stl_doc_types';

    protected $fillable = [
        'id',
        'doc_type_name',
        'is_active',
    ];
}

