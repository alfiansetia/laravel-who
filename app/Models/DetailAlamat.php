<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailAlamat extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function alamat()
    {
        return $this->belongsTo(Alamat::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
