<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_name',
        'branch_name',
        'address',
        'city',
        'pincode',
        'phone_number',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}











