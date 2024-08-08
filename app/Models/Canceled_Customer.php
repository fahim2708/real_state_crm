<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Canceled_Customer extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function flat_or_plot()
    {
        return $this->belongsTo(FlatOrPlot::class, 'flat_or_plot_id', 'id');
    }
    public function customer_payment()
    {
        return $this->hasMany(Cancel_Customer_Payment:: class, 'cancel_customer_id', 'id');
    }
    public function projectInfo()
    {
        return $this->hasOneThrough(Project::class,FlatOrPlot::class,'id','id','id' ,'project_id');
    }
}
