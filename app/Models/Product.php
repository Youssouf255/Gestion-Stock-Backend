<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'product_id',
        'category_id',
        'supplier_id',
        'buying_price',
        'selling_price',
        'quantity',
        'opening_stock',
        'remaining_stock',
        'on_the_way',
        'unit',
        'expiry_date',
        'threshold_value',
        'image',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    protected $appends = ['availability'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getAvailabilityAttribute()
    {
        if ($this->remaining_stock <= 0) {
            return 'out-of-stock';
        } elseif ($this->remaining_stock <= $this->threshold_value) {
            return 'low-stock';
        }
        return 'in-stock';
    }
}











