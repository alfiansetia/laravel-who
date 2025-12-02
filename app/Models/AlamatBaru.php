<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlamatBaru extends Model
{
    protected $guarded = ['id'];

    public function kolis()
    {
        return $this->hasMany(Koli::class);
    }
}
