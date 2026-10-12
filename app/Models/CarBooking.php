<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id', 'title', 'pickup_date', 'pickup_start_time', 'pickup_end_time',
    ];
    

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

