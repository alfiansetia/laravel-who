<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'name',
        'group',
        'akl',
        'akl_exp',
        'akl_file',
        'category',
        'vendor',
        'desc',
    ];
}
