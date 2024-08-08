<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlotOrFlatDetails extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function plotOrFlatRegistration()
    {
        return $this->hasOne(PlotOrFlatRegistration::class, 'plot_or_flat_detailes_id', 'id');
    }

}
