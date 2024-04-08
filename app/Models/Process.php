<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    use HasFactory;
	protected $table='stl_item_description';
    public $timestamps = false;

    public function clients()
    {
        return $this->belongsTo(Client::class, 'clients_id');
    }

  

}
