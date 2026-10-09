<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bast extends Model
{
    use HasFactory;

    protected $fillable = ['do', 'name', 'address', 'city'];

    public function details(): HasMany
    {
        return $this->hasMany(DetailBast::class, 'bast_id')->orderBy('order');
    }
}
