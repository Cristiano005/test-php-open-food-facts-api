<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    protected $primaryKey = 'code';
    protected $fillable = ['code', 'product_name', 'quantity', 'url', 'creator', 'created_t', 'created_datetime', 'imported_t', 'status'];
    public $incrementing = false; 
    public $timestamps = false;

    use HasFactory;
}
