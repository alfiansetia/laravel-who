<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function target()
    {
        return $this->belongsTo(Target::class);
    }
}
