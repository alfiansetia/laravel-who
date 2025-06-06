<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'id'        => 'integer',
        'odoo_id'   => 'integer',
    ];

    public function pls()
    {
        return $this->hasMany(PackingList::class);
    }

    public function target()
    {
        return $this->hasOne(Target::class);
    }
}
