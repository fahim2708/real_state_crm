<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nominee extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function customers(){
        return $this->belongsTo(Customer::class,'id','customer_id');
    }

}
