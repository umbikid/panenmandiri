<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = ['id'];

    public function category() { return $this->belongsTo(Category::class); }
    public function subcategory() { return $this->belongsTo(Subcategory::class); }
    public function partner() { return $this->belongsTo(User::class, 'partner_id'); }
    public function photos() { return $this->hasMany(ProductPhoto::class); }
    public function primaryPhoto() { return $this->hasOne(ProductPhoto::class)->where('is_primary', true); }
    public function coverages() { return $this->hasMany(ProductCoverage::class); }
}