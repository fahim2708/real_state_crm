<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationStatus extends Model
{
    use HasFactory;

    protected $guarded = [];
    public static $COMPLETE = 1;
    public static $PENDING = 2;
    public static $NOT_PROCESSING = 3;
    public static $NOT_APPLICABLE = 4;

    public static $REGISTRATION = "flat_or_plot_registration_status";
    public static $MUTATION = "mutation_cost_status";
    public static $POWER_OF_ATTORNEY = "power_of_attorney_cost_status";

    public function flat_or_plot()
    {
        return $this->belongsTo(FlatOrPlot::class, 'flat_or_plot_id', 'id');
    }
}
