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
        'id', 'order_id', 'order_date', 'process_id', 'state_id', 'county_id', 'status_id', 'assignee_user_id', 'assignee_qa_id', 'created_by', 'is_active'
    ];

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
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
}
