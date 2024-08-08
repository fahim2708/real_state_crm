<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationPaymentHistory extends Model
{
    use HasFactory;

    public function registration_amount()
    {
        return $this->belongsTo(RegistrationAmount::class, 'registration_amount_id', 'id');
    }
}
