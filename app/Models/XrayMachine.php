<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XrayMachine extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'machine_name',
        'SNu',
        'status',
        'image'
    ];

    // Many-to-Many Relationship
    public function cars()
    {
        return $this->belongsToMany(Car::class, 'car_xray_machine');
    }
}
