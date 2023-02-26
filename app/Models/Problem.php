<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Problem extends Model
{
    use HasFactory;
    protected $fillable = [
        'date',
        'type',
        'number',
        'pic',
        'status'
    ];

    public function problemdt()
    {
        return $this->hasMany(Problemdt::class);
    }

    public function problemlog()
    {
        return $this->hasMany(Problemlog::class);
    }
}
