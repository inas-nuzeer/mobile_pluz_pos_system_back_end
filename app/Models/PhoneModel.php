<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\TenantTrait;

class PhoneModel extends Model
{
    /** @use HasFactory<\Database\Factories\PhoneModelFactory> */
    use HasFactory, TenantTrait;

    protected $fillable = ['shop_id', 'brand_id', 'name'];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
