<?php

namespace App\Models;

use App\Services\AklFileStorage;
use Illuminate\Database\Eloquent\Model;

class Akl extends Model
{
    protected $guarded = ['id'];

    protected $appends = ['url', 'is_pdf', 'is_expired'];

    protected $casts = [
        'date_from' => 'date',
        'date_expired' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($akl) {
            if ($akl->file) {
                AklFileStorage::delete($akl->file);
            }
        });
    }

    public function getUrlAttribute()
    {
        return AklFileStorage::url($this->file);
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->file && strtolower(pathinfo($this->file, PATHINFO_EXTENSION)) === 'pdf';
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->date_expired && $this->date_expired->isPast();
    }

    public function items()
    {
        return $this->hasMany(AklItem::class);
    }
}
