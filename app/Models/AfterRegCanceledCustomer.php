<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AfterRegCanceledCustomer extends Model
{
    use HasFactory;
    public function flat_or_plot()
    {
        return $this->belongsTo(FlatOrPlot::class, 'flat_or_plot_id', 'id');
    }
    public function customer_payment()
    {
        return $this->hasMany(AfterRegCancelCustomerPayment:: class, 'after_reg_cancel_customer_id', 'id');
    }
    public function projectInfo()
    {
        return $this->hasOneThrough(Project::class,FlatOrPlot::class,'id','id','id' ,'project_id');
    }
    
    public function buyBackData()
    {
        return $this->hasOne(RegistrationBuyBack:: class, 'after_reg_cancel_customer_id', 'id');
    }
}
