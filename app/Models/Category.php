<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = ['id'];

    public function subcategories() { return $this->hasMany(Subcategory::class)->orderBy('sort_order'); }
    public function products() { return $this->hasMany(Product::class); }
}