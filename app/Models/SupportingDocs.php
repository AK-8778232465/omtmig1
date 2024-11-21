<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportingDocs extends Model
{
    use HasFactory;
    protected $table = 'supporting_docs';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'order_id',
        'pdf_file',
        'created_at'
    ];

    // public function orders()
    // {
    //     return $this->hasMany(Order::class, 'id');
    // }
}
