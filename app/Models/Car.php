<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'license_plate',
        'image',
        'service_status',
        'department',
    ];

    // กรองรถตามแผนก: Car::department('checkup')
    public function scopeDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    // ชื่อแผนกเจ้าของรถ เช่น 'Checkup'
    public function getDepartmentLabelAttribute(): string
    {
        return User::DEPARTMENTS[$this->department] ?? (string) $this->department;
    }

    // Many-to-Many Relationship
    public function xrayMachines()
    {
        return $this->belongsToMany(XrayMachine::class, 'car_xray_machine');
    }
    public function getServiceStatusAttribute($value)
    {
        return ucfirst($value); // Capitalize the first letter
    }

    // Mutator for 'service_status'
    public function setServiceStatusAttribute($value)
    {
        $this->attributes['service_status'] = strtolower($value); // Ensure it's lowercase
    }
}

