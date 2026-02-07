<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $primaryKey = 'code';
    protected $fillable = ['code', 'product_name', 'quantity', 'url', 'creator', 'created_t', 'created_datetime', 'imported_t', 'status'];
    public $timestamps = false;

    use HasFactory;
}
