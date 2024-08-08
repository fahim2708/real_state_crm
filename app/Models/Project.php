<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $guarded=[];

    public static $BUILDING = 1;
    public static $LAND     = 2;

    public function flatOrPlot()
    {
        return $this->hasMany(FlatOrPlot::class, 'project_id', 'id');
    }

//    public function plot()
//    {
//        return $this->hasMany(Plot::class,'project_id','id');
//    }





}
