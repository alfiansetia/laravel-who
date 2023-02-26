<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Problemlog extends Model
{
    use HasFactory;
    protected $fillable = [
        'problem_id',
        'date',
        'desc',
    ];

    public function problem()
    {
        return $this->belongsTo(Problem::class);
    }
}
