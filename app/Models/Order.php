<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'supplier_id',
        'store_id',
        'order_id',
        'order_value',
        'quantity',
        'unit',
        'buying_price',
        'expected_delivery',
        'status',
        'notify_on_delivery',
    ];

    protected $casts = [
        'expected_delivery' => 'date',
        'notify_on_delivery' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}











