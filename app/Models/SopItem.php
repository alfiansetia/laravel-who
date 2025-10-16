<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SopItem extends Model
{
    protected $guarded = ['id'];

    public function sop()
    {
        return $this->belongsTo(Sop::class);
    }
}
