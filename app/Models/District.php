<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $guarded = ['id'];

    public function regency()
    {
        return $this->belongsTo(Regency::class);
    }
}
