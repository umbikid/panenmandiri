<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCoverage extends Model
{
    protected $guarded = ['id'];
    
    public function regency() { return $this->belongsTo(Regency::class); }
}