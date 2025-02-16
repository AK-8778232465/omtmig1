<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCreation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'oms_order_creations';

    protected $fillable = [
        'id', 'order_id', 'order_date', 'process_id', 'typist_qc_id','typist_id','state_id', 'county_id','city_id' ,'status_id', 'assignee_user_id', 'assignee_qa_id', 'associate_id', 'lob_id','tier_id','product_id','product_id','created_by', 'is_active', 'completion_date'
    ];

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }
    public function Tier()
    {
        return $this->belongsTo(Tier::class, 'Tier_id');
    }

    public function county()
    {
        return $this->belongsTo(County::class, 'county_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function assignee_user()
    {
        return $this->belongsTo(User::class, 'assignee_user_id');
    }

    public function assignee_qa()
    {
        return $this->belongsTo(User::class, 'assignee_qa_id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function client()
    {
        return $this->hasOneThrough(Client::class, Process::class, 'id', 'id', 'process_id', 'client_id');
    }

    public function product()
    {
        return $this->hasOneThrough(Product::class, Process::class, 'id', 'id', 'process_id', 'client_id');

    }
 
}
