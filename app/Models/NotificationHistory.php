<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationHistory extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function resends(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
