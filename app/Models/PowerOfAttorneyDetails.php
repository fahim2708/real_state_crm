<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PowerOfAttorneyDetails extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function powerOfAttorneyRegistration()
    {
        return $this->hasOne(PlotOrFlatRegistration::class, 'power_of_attorney_details_id', 'id');
    }
}
