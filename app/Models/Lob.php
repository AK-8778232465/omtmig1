<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lob extends Model
{
    use HasFactory;
	protected $table='stl_lob';
    public $timestamps = false;
  
    public function products()
    {
        return $this->hasMany(Product::class, 'lob_id', 'id');
    }
}
