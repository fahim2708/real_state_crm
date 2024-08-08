<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function price_information()
    {
        return $this->belongsTo(PriceInformation::class, 'price_information_id', 'id');
    }
}
