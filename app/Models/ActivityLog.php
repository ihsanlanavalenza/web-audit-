<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'model_type',
        'model_id',
        'action',
        'description',
        'old_payload',
        'new_payload',
    ];

    protected $casts = [
        'old_payload' => 'array',
        'new_payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo('subject', 'model_type', 'model_id');
    }
}
