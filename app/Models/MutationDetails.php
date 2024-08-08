<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutationDetails extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function plotOrFlatRegistrationForMutation()
    {
        return $this->hasOne(PlotOrFlatRegistration::class, 'mutation_detailes_id', 'id');
    }
    public function registration_amount()
    {
        return $this->hasOne(PlotOrFlatRegistration::class, 'mutation_detailes_id', 'id');
    }
}
