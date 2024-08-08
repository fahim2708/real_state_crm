<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceInformation extends Model
{
    use HasFactory;

    protected  $guarded=[];

    public static $BOOKING_MONEY          = 1;
    public static $CAR_PARKING            = 2;
    public static $UTILITY_CHARGE         = 3;
    public static $ADDITIONAL_AMOUNT      = 4;
    public static $DOWNPAYMENT_AMOUNT     = 5;
    public static $INSTALLMENT_AMOUNT     = 6;


    public function downPayment()
    {
        return $this->hasMany(Downpayment::class,'price_information_id','id');
    }

    //new
    public function additional_amount()
    {
        return $this->hasMany(AdditionalAmount::class,'price_information_id','id');
    }

    public function installment()
    {
        return $this->hasMany(Installment::class,'price_information_id','id');
    }

    public  function  flatOrPlot()
    {
        return $this->belongsTo(FlatOrPlot::class,'flat_or_plot_id','id');
    }

    public function paymentHistory()
    {
        return $this->hasMany(PaymentHistory::class, 'price_information_id','id');
    }

}
