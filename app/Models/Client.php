<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $table = 'stl_client';
    public $timestamps = false;

    public function companies()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function states()
    {
        return $this->belongsTo(State::class, 'state_code', 'id');
    }

    public function counties()
    {
        return $this->belongsTo(County::class, 'county_id', 'id');
    }

    public function processes()
    {
        return $this->hasMany(Process::class, 'client_id');
    }
}

