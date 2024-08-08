<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlotOrFlatRegistration extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function flatOrPlotDetails()
    {
        return $this->belongsTo( FlatOrPlot::class, 'flat_or_plots_id', 'id');
    }
    public function flatOrPlotForMutation()
    {
        return $this->belongsTo( FlatOrPlot::class, 'flat_or_plots_id', 'id');
    }

    public function flatOrPlotForPowerOfAttorney()
    {
        return $this->belongsTo( FlatOrPlot::class, 'flat_or_plots_id', 'id');
    }

    public function registration_amount()
    {
        return $this->hasOne(RegistrationAmount::class, 'flat_or_plot_id', 'id');
    }
    
    public function customers()
    {
        return $this->hasManyThrough(Customer::class, SoldItem::class, 'flat_or_plot_id', 'id', 'id', 'customer_id');
    }

    public function plotOrFlatDetails()
    {
        return $this->belongsTo(PlotOrFlatDetails::class, 'plot_or_flat_detailes_id' , 'id');
    }

    public function mutationDetails()
    {
        return $this->belongsTo(MutationDetails::class, 'mutation_detailes_id', 'id');
    }
    public function priceInformation()
    {
        return $this->hasOne(PriceInformation::class,'flat_or_plot_id','id');
    }
    public function project()
    {
        return $this->hasOneThrough(Project::class,FlatOrPlot::class,'id','id','id' ,'project_id');
    }
}
