<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtkTransaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function atk()
    {
        return $this->belongsTo(Atk::class);
    }
}
