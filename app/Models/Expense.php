<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\TenantTrait;

class Expense extends Model
{
    /** @use HasFactory<\Database\Factories\ExpenseFactory> */
    use HasFactory, TenantTrait;

    protected $fillable = ['shop_id', 'name', 'amount', 'date', 'category'];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
