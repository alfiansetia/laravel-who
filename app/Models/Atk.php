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
        return $this->transactions()
            ->selectRaw("SUM(CASE WHEN type = 'in' THEN qty ELSE 0 END) - SUM(CASE WHEN type = 'out' THEN qty ELSE 0 END) as stok")
            ->value('stok') ?? 0;
    }
}
