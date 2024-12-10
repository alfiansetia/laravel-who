<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProblemItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function problem()
    {
        return $this->belongsTo(Problem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
