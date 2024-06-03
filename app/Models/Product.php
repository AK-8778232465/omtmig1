<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
	protected $table='oms_products';
    public $timestamps = false;
  
    protected $fillable = [ 'client_id', 'lob_id', 'product_name', 'is_active', 'created_by', 'updated_by'];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }
}
