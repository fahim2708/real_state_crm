<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlatOrPlot extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static  $SOLD = 1;
    public static $UNSOLD = 0;

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
    public function plotOrFlatRegistration()
    {
        return $this->hasOne(PlotOrFlatRegistration::class, 'flat_or_plots_id', 'id');
    }

    public function customers()
    {
        return $this->hasManyThrough(Customer::class,SoldItem::class,'flat_or_plot_id','id','id' ,'customer_id');
    }

    public function priceInformation()
    {
        return $this->hasOne(PriceInformation::class,'flat_or_plot_id','id');
    }

    public function registration_amount()
    {
        return $this->hasOne(RegistrationAmount::class, 'flat_or_plot_id', 'id');
    }

    public function registration_status()
    {
        return $this->hasOne(RegistrationStatus::class, 'flat_or_plot_id', 'id');
    }
    //relation for Canceled Customer Controller
    public function projectInfo()
    {
        return $this->hasOneThrough(Project::class,FlatOrPlot::class,'id','id','id' ,'project_id');
    }
    public function soldItems()
    {
        return $this->hasOne(SoldItem::class,'flat_or_plot_id','id');
    }
}
