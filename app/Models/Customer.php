<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function soldItem()
    {
        return $this->hasOne(SoldItem::class,'customer_id','id');
    }

    public function profile()
    {
        return $this->hasOne(CustomerProfile::class,'customer_id','id');
    }

    public function document()
    {
        return $this->hasOne(CustomerDocument::class,'customer_id','id');
    }

    public function nominee()
    {
        return $this->hasOne(Nominee::class,'customer_id','id');
    }

}
