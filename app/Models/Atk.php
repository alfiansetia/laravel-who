<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Atk extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function transactions()
    {
        return $this->hasMany(AtkTransaction::class)->orderBy('date', 'asc');
    }

    public function getStokAttribute()
    {
        $in = $this->transactions()
            ->where('type', 'in')
            ->sum('qty');

        $out = $this->transactions()
            ->where('type', 'out')
            ->sum('qty');

        return $in - $out;
    }
}
