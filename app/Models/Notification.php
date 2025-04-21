<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'type',
        'message',
        'read'
    ];

    protected $casts = [
        'read' => 'boolean'
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
