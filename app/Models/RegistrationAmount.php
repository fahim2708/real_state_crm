<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationAmount extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static $REGISTRY = 1;
    public static $MUTATION = 2;
    public static $POWER_OF_ATTORNEY = 3;

    public function flat_or_plot()
    {
        return $this->belongsTo(FlatOrPlot::class, 'flat_or_plot_id', 'id');
    }

    public function registration_payment_history()
    {
        return $this->hasMany(RegistrationPaymentHistory::class, 'registration_amount_id', 'id');
    }
}
