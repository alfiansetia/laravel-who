<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Problemdt extends Model
{
    use HasFactory;
    protected $fillable = [
        'problem_id',
        'product_id',
        'snlot',
        'qc',
        'status',
        'ri',
        'desc'
    ];

    public function problem()
    {
        return $this->belongsTo(Problem::class);
    }
}
